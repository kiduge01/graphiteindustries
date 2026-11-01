# Leadership portraits

Five supplied portraits enhanced with the built-in image generation tool on 31 October 2026. Original uploads in the project root are retained unchanged. The PNG files in this directory are the enhanced master copies; responsive WebP delivery copies are in `public/assets/team/` at widths of 480 and 960 pixels.

## Source mapping

| Enhanced master | Uploaded original |
| --- | --- |
| amini-kimaro.png | Kimaro PPT Photo.jpg.jpeg |
| juliette-kimaro.png | Juliette PPT Photos.jpeg |
| denis-kimaro.png | Denis A. Kimaro PPT Size Photo.jpg.jpeg |
| nickson-kimbori.png | Nikson J. Kimbori _rvsd.jpeg |
| ridhiwani-mbaruku.png | Ridhiwan PPT Photo.jpeg |

Website display names follow the supplied leadership list rather than normalizing them from file names. No photos were supplied for Dr. Humphrey Musyoka, Dr. Alfred C. Msemo or Harish Kumar; those profiles use initials.

## Page implementation and verification

The About Us page now uses the selected compact leadership spotlight. A featured portrait and role sit beside Board and Management selectors; each group preserves the supplied order. Arrow controls, optional playback, an enlarged portrait viewer and reduced-motion support are provided by `public/assets/leadership-spotlight.js` and styled by `public/assets/leadership-spotlight.css`. Full names and roles remain in the server-rendered fallback before JavaScript.

The page is shortened to the company introduction, company story, original vision and mission, leadership and a closing contact invitation. Repeated Focus, Strategy and Approach sections and the illustrative team collage are removed from About Us; the Project and Sustainability pages retain their detailed content.

`app/about-sample-a.html` is the source template. After changing it, run `node bin/sync-about-template.cjs` to synchronize the JavaScript presentation. `site.js` initializes the spotlight after rendering, and the spotlight script also enhances the server-rendered page independently. Updated assets have cache versions in `public/index.html`.

Verified the spotlight at widths of 320, 390, 768, 1024, 1280 and 1440 pixels: no page overflow or clipped names, all portraits load, and the card height remains stable when groups change. The card measures 396px tall at 1440px and 511px at 390px. Verified group selection, previous/next controls, the portrait viewer and Escape, optional playback and pause, reduced motion, idempotent initialization, server fallback, template parity, JavaScript syntax and Git whitespace checks. Current captures: `storage/compact-spotlight-1440.png`, `storage/compact-spotlight-390.png` and `storage/compact-about-desktop.png`.

## Final prompt (used separately for each supplied image)

Use case: identity-preserve. Edit target: the single supplied real person's portrait. Task: conservatively restore and enhance this exact photo for a company leadership website. Preserve the person's exact identity and facial geometry, expression, gaze, age, hair, facial hair, skin tone, clothing, body position and all distinguishing facial marks. Improve photographic clarity, gently reduce compression/noise and correct exposure and color cast; recover detail only conservatively, preserve natural skin texture. Remove photo scan border and minor scanning dust if present. Replace only the flat backdrop with a clean consistent very pale warm gray studio background (#f2f3f3). Preserve hair edges carefully. Keep the same crop and proportions and visible clothing; do not invent additional body, change outfit, add a suit, beautify, smooth away age, change facial structure, change eyes or add smile. This must clearly remain the very same supplied photograph, subtly restored, not reimagined. Produce a single unframed portrait image, no text, labels, graphics, watermarks or composite.
