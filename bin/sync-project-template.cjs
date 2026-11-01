// Shared structure for the PHP response and the JavaScript-enhanced project page.
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const html = fs.readFileSync(path.join(root, 'app/project-layout.html'), 'utf8');
const encoded = JSON.stringify(html).replace(/</g, '\\u003c').replace(/>/g, '\\u003e').replace(/&/g, '\\u0026');
fs.writeFileSync(path.join(root, 'public/assets/project-layout.js'),
  `'use strict';\nfunction projectLayout(slots) { return ${encoded}.replace(/\\{\\{(\\w+)\\}\\}/g, (_, key) => slots[key] || ''); }\n`);
console.log('Project browser template synchronized.');
