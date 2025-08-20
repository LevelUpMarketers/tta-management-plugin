# Profile Image Popup

Elements with the `tta-popup-img` class display a larger version in a simple overlay when clicked. The popup is handled by `profile-popup.js` and disabled automatically when the viewport width is 480px or less.

The script and its accompanying stylesheet are loaded on the Event Page, Events List template, and the admin Event screens. To use it elsewhere simply add the class and optional `data-full` attribute to an image element:

```html
<img src="thumbnail.jpg" data-full="large.jpg" class="tta-popup-img" alt="Profile">
```

Clicking the thumbnail opens an overlay showing the `data-full` image or falls back to the image's own `src` attribute.
If the image has an `alt` attribute, that text appears beneath the popup as a caption.

