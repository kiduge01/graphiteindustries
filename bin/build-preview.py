from pathlib import Path
import shutil
root=Path(__file__).resolve().parent.parent
out=root/'dist'
shutil.rmtree(out,ignore_errors=True)
out.mkdir()
shutil.copytree(root/'public/assets',out/'assets')
for name in ['index.html','admin.html']:
 text=(root/'public'/name).read_text().replace('<html lang="en">','<html lang="en" data-preview="true">').replace('index.php','index.html').replace('admin.php','admin.html')
 (out/name).write_text(text)
for name in ['site.js','admin.js']:
 p=out/'assets'/name
 p.write_text(p.read_text().replace('index.php','index.html').replace('admin.php','admin.html'))
(out/'robots.txt').write_text('User-agent: *\nDisallow: /\n')
print('Static design preview built. PHP application remains in public/.')
