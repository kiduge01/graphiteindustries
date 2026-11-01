// Rebuild the local, public-domain country orientation map.
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
(async () => {
  const source = 'https://raw.githubusercontent.com/nvkelso/natural-earth-vector/master/geojson/ne_110m_admin_0_countries.geojson';
  const response = await fetch(source, {signal:AbortSignal.timeout(60000)});
  if (!response.ok) throw Error('Natural Earth download failed: '+response.status);
  const collection = await response.json();
  const feature = collection.features.find(x => x.properties.ADMIN === 'United Republic of Tanzania' || x.properties.ADM0_A3 === 'TZA');
  if (!feature) throw Error('Tanzania not found.');
  fs.writeFileSync(path.join(root, 'assets/images/tanzania-natural-earth.geojson'), JSON.stringify(feature,null,2)+'\n');
  const polygons = feature.geometry.type === 'Polygon' ? [feature.geometry.coordinates] : feature.geometry.coordinates;
  const project = ([lon,lat]) => [(lon-28)*29+68,(-lat+1)*29+22];
  const paths = polygons.map(poly=>poly.map(ring=>ring.map((point,i)=>{const [x,y]=project(point);return (i?'L':'M')+x.toFixed(1)+','+y.toFixed(1)}).join('')+'Z').join('')).join('');
  // Broad orientation marker, not a property or a facility coordinate.
  const [x,y]=project([36.3,-6.7]);
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 480" role="img" aria-labelledby="title description">
<title id="title">Mpwapwa area, Tanzania</title><desc id="description">Tanzania country outline with a broad marker for the Mpwapwa area in Dodoma Region. General location only; exact project boundaries are not shown.</desc>
<rect width="600" height="480" fill="#edf2ef"/>
<g fill="none" stroke="#dce5df" stroke-width="1"><path d="M0 120H600M0 240H600M0 360H600M120 0V480M240 0V480M360 0V480M480 0V480"/></g>
<path d="${paths}" fill="#d5e1d8" stroke="#809b8a" stroke-width="1.5" stroke-linejoin="round"/>
<g font-family="Arial,sans-serif" fill="#174a3a"><text x="40" y="45" font-size="11" letter-spacing="2">EAST AFRICA</text><text x="195" y="168" font-size="18" letter-spacing="3">TANZANIA</text>
<circle cx="${x}" cy="${y}" r="24" fill="#174a3a" opacity=".1"/><circle cx="${x}" cy="${y}" r="13" fill="none" stroke="#174a3a" stroke-dasharray="3 3"/><circle cx="${x}" cy="${y}" r="4" fill="#174a3a"/>
<path d="M${x+17} ${y}h44" stroke="#174a3a" fill="none"/><text x="${x+66}" y="${y+4}" font-size="17" font-weight="600">Mpwapwa</text><text x="${x+66}" y="${y+24}" font-size="12" fill="#535d60">Dodoma Region</text>
<path d="M542 78V46m-5 8 5-8 5 8" stroke="#174a3a" fill="none"/><text x="538" y="36" font-size="10">N</text>
<text x="40" y="437" font-size="11" fill="#535d60">GENERAL AREA · PROJECT BOUNDARIES NOT SHOWN</text></g></svg>\n`;
  fs.writeFileSync(path.join(root, 'public/assets/mpwapwa-location.svg'), svg);
  console.log('Created local Tanzania orientation map from Natural Earth.');
})().catch(error=>{console.error(error);process.exitCode=1});
