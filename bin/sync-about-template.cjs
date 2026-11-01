// Keep the JavaScript-enhanced page identical to the server-rendered About page.
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const html = fs.readFileSync(path.join(root, 'app/about-sample-a.html'), 'utf8');
const encoded = JSON.stringify(html)
  .replace(/</g, '\\u003c')
  .replace(/>/g, '\\u003e')
  .replace(/&/g, '\\u0026');
fs.writeFileSync(path.join(root, 'public/assets/about-sample-a.js'),
  `'use strict';\nfunction aboutSampleA() { return ${encoded}; }\n`);
console.log('About Us browser template synchronized.');
