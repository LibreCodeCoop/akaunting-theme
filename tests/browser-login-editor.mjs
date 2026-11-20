// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

// Run the Blade editor's actual JavaScript in an isolated Chrome fixture.
// No production requests, customer records, authentication or network.
import assert from 'node:assert/strict';
import { spawn } from 'node:child_process';
import { mkdtempSync, readFileSync, writeFileSync, rmSync, existsSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { pathToFileURL } from 'node:url';

const source = readFileSync(resolve(import.meta.dirname, '../Resources/views/components/global-slide-editor.blade.php'), 'utf8');
const match = source.match(/<script id="lcc-global-slide-editor-script">([\s\S]*?)<\/script>/);
assert.ok(match, 'Editor JavaScript missing');
const script = match[1].replaceAll("@json(trans('theme::general.carousel_slide'))", '"Slide"');
assert.ok(!script.includes('@json('), 'Blade directive was not translated');

const dir = mkdtempSync(join(tmpdir(), 'lcc-theme-editor-'));
const path = join(dir, 'fixture.html');
const saved = i =>
    '<article data-slide-card data-slide-index="' + i + '" data-slide-saved="1">' +
    '<h3>Slide ' + (i+1) + '</h3><button data-slide-remove type="button">Delete</button>' +
    '<input type="hidden" data-slide-delete-flag name="delete_slide[' + i + ']" value="0">' +
    '<div data-slide-deleted hidden>Removed <button data-slide-undo type="button">Undo</button></div>' +
    '<div data-slide-fields class="grid"><input name="slide_title[' + i + ']" value="Saved ' + i + '">' +
    '<textarea name="slide_caption[' + i + ']"></textarea>' +
    '<input type="file" name="slide_image[' + i + ']"></div></article>';
const html = [
    '<!doctype html><meta charset="utf-8"><style>.grid{display:grid}</style><form><div data-slide-editor data-max-slides="6">',
    '<div data-slide-list>', saved(0), saved(1), '</div>',
    '<p data-slide-empty hidden>No slides</p>',
    '<button type="button" data-slide-add>Add</button>',
    '<template data-slide-template>',
    '<article data-slide-card data-slide-index="__INDEX__" data-slide-saved="0">',
    '<h3>Slide __NUMBER__</h3><button type="button" data-slide-remove>Delete</button>',
    '<div data-slide-fields class="grid"><input name="slide_title[__INDEX__]">',
    '<textarea name="slide_caption[__INDEX__]"></textarea>',
    '<input name="slide_image[__INDEX__]" type="file"></div></article>',
    '</template></div></form>',
    '<script>', script, '</script>',
].join('');
writeFileSync(path, html);

const sleep = ms => new Promise(done => setTimeout(done, ms));
const process = spawn('google-chrome', ['--headless=new', '--no-sandbox', '--disable-gpu',
    '--disable-dev-shm-usage', '--disable-background-networking', '--remote-allow-origins=*',
    '--remote-debugging-port=0', '--user-data-dir=' + join(dir,'profile'), 'about:blank'], {stdio:'ignore'});

try {
    const portFile = join(dir,'profile','DevToolsActivePort');
    let port=0;
    for(let i=0;i<150&&!port;i++) {
        if(existsSync(portFile)) port=Number(readFileSync(portFile,'utf8').split('\n')[0]);
        else await sleep(100);
    }
    assert.ok(port, 'Chrome did not launch');
    const response=await fetch('http://127.0.0.1:'+port+'/json');
    const pages=await response.json();
    const ws=new WebSocket(pages.find(page=>page.type==='page').webSocketDebuggerUrl);
    await new Promise(done=>ws.addEventListener('open',done,{once:true}));
    let seq=0; const pending=new Map();
    ws.addEventListener('message',({data})=>{
        const result=JSON.parse(data);
        const resolve=pending.get(result.id);
        if(resolve){pending.delete(result.id);resolve(result);}
    });
    const call=(method, params={})=>new Promise(resolve=>{
        const id=++seq;pending.set(id,resolve);
        ws.send(JSON.stringify({id,method,params}));
    });
    await call('Page.enable');
    await call('Runtime.enable');
    await call('Page.navigate',{url:pathToFileURL(path).href});
    await sleep(750);
    const expression=[
        '(()=>{',
        'const root=document.querySelector("[data-slide-editor]");',
        'const cards=()=>Array.from(root.querySelectorAll("[data-slide-card]"));',
        'const active=()=>cards().filter(c=>c.dataset.slideDeleted!=="1");',
        'const add=()=>root.querySelector("[data-slide-add]").click();',
        'const result={initial:active().length};',
        'add();result.added=active().length;',
        'result.newInput=!!document.querySelector("[name=\'slide_title[2]\']");',
        'cards().find(c=>c.dataset.slideSaved==="0").querySelector("[data-slide-remove]").click();',
        'result.newRemoved=active().length;',
        'const first=cards()[0];',
        'first.querySelector("[data-slide-remove]").click();',
        'result.marked=first.querySelector("[data-slide-delete-flag]").value;',
        'result.savedTitle=first.querySelector("[name=\'slide_title[0]\']").value;',
        'result.hiddenStyle=getComputedStyle(first.querySelector("[data-slide-fields]")).display;',
        'result.afterSavedRemove=active().length;',
        'first.querySelector("[data-slide-undo]").click();',
        'result.undone=first.querySelector("[data-slide-delete-flag]").value;',
        'result.afterUndo=active().length;',
        'result.undoStyle=getComputedStyle(first.querySelector("[data-slide-fields]")).display;',
        'add();add();add();add();',
        'result.atLimit=active().length===6;',
        'result.disabled=root.querySelector("[data-slide-add]").disabled;',
        'active().at(-1).querySelector("[data-slide-remove]").click();',
        'result.afterRemoval=active().length;',
        'result.addEnabled=!root.querySelector("[data-slide-add]").disabled;',
        'return result;',
        '})()',
    ].join('\n');
    const result=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});
    assert.equal(result.exceptionDetails,undefined,JSON.stringify(result.exceptionDetails));
    assert.deepEqual(result.result.result.value,{
        initial:2,added:3,newInput:true,newRemoved:2,
        marked:'1',savedTitle:'Saved 0',hiddenStyle:'none',afterSavedRemove:1,
        undone:'0',afterUndo:2,undoStyle:'grid',atLimit:true,disabled:true,
        afterRemoval:5,addEnabled:true,
    });
    console.log('PASS: Chrome add, remove, undo, retain saved input, six-slide limit');
    ws.close();
} finally {
    process.kill('SIGTERM');
    await sleep(120);
    rmSync(dir,{recursive:true,force:true});
}
