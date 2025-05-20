// assets/js/image-cropper.js
document.addEventListener('DOMContentLoaded', ()=> {
    let cropper;
    // find the file input by CSS class
    const input = document.querySelector('.js-image-cropper-input');
    if (!input) return;
  
    // preview <img> and hidden crop fields are looked up by data-attributes
    const preview   = document.querySelector(input.dataset.preview);
    const fieldX    = document.querySelector(input.dataset.cropX);
    const fieldY    = document.querySelector(input.dataset.cropY);
    const fieldW    = document.querySelector(input.dataset.cropW);
    const fieldH    = document.querySelector(input.dataset.cropH);
    const form      = input.closest('form');
  
    // when user selects a file, show preview + start cropper
    input.addEventListener('change', e => {
      const file = e.target.files[0];
      if (!file) return;
      const url = URL.createObjectURL(file);
      preview.src = url;
      preview.style.display = 'block';
      if (cropper) cropper.destroy();
      cropper = new Cropper(preview, {
        aspectRatio: 4/3,
        viewMode: 1,
        autoCropArea: 1,
      });
    });
  
    // before form submits, pull out the crop data
    form.addEventListener('submit', () => {
      if (!cropper) return;
      const data = cropper.getData(true);
      fieldX.value = Math.round(data.x);
      fieldY.value = Math.round(data.y);
      fieldW.value = Math.round(data.width);
      fieldH.value = Math.round(data.height);
    });
  });
  