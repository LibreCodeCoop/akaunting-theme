// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

function validateGif(bytes, {width, height, frameCount, transparent}) {
    assert.equal(bytes.subarray(0, 6).toString('ascii'), 'GIF89a');
    assert.equal(bytes.readUInt16LE(6), width);
    assert.equal(bytes.readUInt16LE(8), height);
    assert.ok(bytes.length < 1024 * 1024, 'Asset exceeds theme upload limit');
    assert.ok(bytes.includes(Buffer.from('NETSCAPE2.0')), 'Animation should loop indefinitely');

    let offset = 13;
    const globalPacked = bytes[10];
    if (globalPacked & 0x80) {
        offset += 3 * (2 ** ((globalPacked & 0x07) + 1));
    }
    let frames = 0;
    let pendingTransparency = false;
    const encounteredColors = new Set();
    function skipColorTable(entries) {
        for (let i = 0; i < entries; i++) {
            const rgb = bytes.subarray(offset, offset + 3);
            encounteredColors.add([...rgb].join(','));
            offset += 3;
        }
    }
    // The global color table was skipped above for parsing. Read its colors separately.
    if (globalPacked & 0x80) {
        const start = 13;
        for (let i = start; i < offset; i += 3) {
            encounteredColors.add([...bytes.subarray(i, i + 3)].join(','));
        }
    }
    function skipData() {
        while (true) {
            const length = bytes[offset++];
            if (length === 0) break;
            assert.ok(offset + length <= bytes.length, 'Truncated GIF data block');
            offset += length;
        }
    }
    while (offset < bytes.length) {
        const kind = bytes[offset++];
        if (kind === 0x3b) break;
        if (kind === 0x21) {
            const label = bytes[offset++];
            if (label === 0xf9) {
                assert.equal(bytes[offset++], 4, 'Unexpected graphics control block size');
                pendingTransparency = Boolean(bytes[offset] & 0x01);
                offset += 4;
                assert.equal(bytes[offset++], 0, 'Graphics control terminator missing');
            } else {
                skipData();
            }
        } else if (kind === 0x2c) {
            frames++;
            offset += 8;
            const packed = bytes[offset++];
            if (packed & 0x80) {
                skipColorTable(2 ** ((packed & 0x07) + 1));
            }
            if (transparent) {
                assert.equal(pendingTransparency, true, 'Frame ' + frames + ' has no transparency');
            }
            pendingTransparency = false;
            offset++; // LZW minimum code size
            skipData();
        } else {
            assert.fail('Unexpected GIF byte ' + kind + ' at ' + (offset - 1));
        }
    }
    assert.equal(frames, frameCount, 'Wrong frame count');
    assert.ok(encounteredColors.size > 1, 'Color tables missing');
    return encounteredColors;
}

test('company GIF derived from the supplied animation has a stable transparent background in every frame', () => {
    const bytes = fs.readFileSync(path.join(__dirname, '../Resources/assets/spinners/librecode-loading.gif'));
    const colors = validateGif(bytes, {width: 120, height: 120, frameCount: 20, transparent: true});
    const allowed = new Set(['0,0,0', '233,51,42', '130,132,126']);
    for (const color of colors) {
        assert.ok(allowed.has(color), 'Unexpected potentially opaque background color: ' + color);
    }
});

test('previous LibreCode wordmark loader remains available as an alternative', () => {
    const asset = fs.readFileSync(path.join(__dirname, '../Resources/assets/spinners/librecode-wordmark-loading.gif'));
    const generator = fs.readFileSync(path.join(__dirname, '../scripts/generate_librecode_spinner.py'), 'utf8');
    assert.ok(generator.includes('LibreCode'));
    assert.ok(generator.includes('librecode-wordmark-loading.gif'));
    validateGif(asset, {width: 238, height: 96, frameCount: 24, transparent: false});
});
