// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

test('sample LibreCode loading GIF is looped, within upload limits and reproducible from repo assets', () => {
    const asset = fs.readFileSync(path.join(__dirname, '../Resources/assets/spinners/librecode-loading.gif'));
    assert.equal(asset.subarray(0, 6).toString('ascii'), 'GIF89a');
    assert.equal(asset.readUInt16LE(6), 144);
    assert.equal(asset.readUInt16LE(8), 144);
    assert.ok(asset.byteLength <= 1024 * 1024, 'GIF exceeds the module upload limit');
    assert.ok(asset.includes(Buffer.from('NETSCAPE2.0')), 'Missing infinite animation loop');
    assert.ok(asset.includes(Buffer.from([0x21, 0xF9, 0x04])), 'Missing GIF frames');
});
