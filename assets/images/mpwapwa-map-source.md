# Mpwapwa orientation map

`public/assets/mpwapwa-location.svg` is an original SVG layout based on the public-domain Natural Earth 1:110m Tanzania country outline. The extracted country feature is retained in `tanzania-natural-earth.geojson`.

- Geometry: https://github.com/nvkelso/natural-earth-vector/blob/master/geojson/ne_110m_admin_0_countries.geojson
- Public-domain terms: https://www.naturalearthdata.com/about/terms-of-use/
- General district location: https://www.geonames.org/153093/mpwapwa.html (Dodoma Region; rounded to 36.3° E, 6.7° S for orientation).

The broad ring indicates the general Mpwapwa area. It does not identify a facility, mineral right, surveyed site, district boundary or project landholding. The map contains its own location labels; the page adds no image captions. No remote map scripts, embedded tracking, API key or map tiles are loaded. The external link opens an OpenStreetMap place search only when selected.

Rebuild helper: `node bin/build-project-map.cjs` (downloads country geometry).
