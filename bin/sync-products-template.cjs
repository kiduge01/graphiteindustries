const fs=require('node:fs'),path=require('node:path');
const root=path.resolve(__dirname,'..');
const html=fs.readFileSync(path.join(root,'app/products-layout.html'),'utf8');
const encoded=JSON.stringify(html).replace(/</g,'\\u003c').replace(/>/g,'\\u003e').replace(/&/g,'\\u0026');
fs.writeFileSync(path.join(root,'public/assets/products-layout.js'),`'use strict';\nfunction productsLayout(slots) { return ${encoded}.replace(/\\{\\{(\\w+)\\}\\}/g,(_, key)=>slots[key] || ''); }\n`);
console.log('Products browser template synchronized.');
