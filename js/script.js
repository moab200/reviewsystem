let profile = document.querySelector('.header .flex .profile');

document.querySelector('#user-btn').onclick = () =>{
   profile.classList.toggle('active');
}

window.onscroll = () =>{
   profile.classList.remove('active');
}

document.querySelectorAll('input[type="number"]').forEach(inputNumber => {
   inputNumber.oninput = () =>{
      if(inputNumber.value.length > inputNumber.maxLength) inputNumber.value = inputNumber.value.slice(0, inputNumber.maxLength);
   };
});

function previewMedia(event) {
   const input = event.target;
   const files = input.files;
   const mediaContainer = document.getElementById('uploaded-media');

   if(files.length > 0) {
     document.getElementById('add-more-btn').style.display = 'inline-block';
   }

   for (let i = 0; i < files.length; i++) {
       const file = files[i];
       if (file) {
           const reader = new FileReader();
           reader.onload = function(e) {
               const mediaBox = document.createElement('div');
               mediaBox.classList.add('media-box');

               if (file.type.startsWith('image/')) {
                   const img = document.createElement('img');
                   img.src = e.target.result;
                   mediaBox.appendChild(img);
               } else if (file.type.startsWith('video/')) {
                   const video = document.createElement('video');
                   video.src = e.target.result;
                   video.controls = true;
                   mediaBox.appendChild(video);
               }

               const removeBtn = document.createElement('button');
               removeBtn.classList.add('remove-btn');
               removeBtn.textContent = '';
               removeBtn.onclick = function() {
                   mediaBox.remove();
                   if (mediaContainer.querySelectorAll('.media-box').length === 0) {
                       document.getElementById('add-more-btn').style.display = 'none'; // Hide the "Add more" button if no media is present
                   }
               };

               mediaBox.appendChild(removeBtn);
               mediaContainer.appendChild(mediaBox);

               // Hide the input after one file is selected
               input.style.display = 'none';
           };
           reader.readAsDataURL(file);
       }
   }
}

function addMoreMedia() {
   const newInput = document.createElement('input');
   newInput.type = 'file';
   newInput.name = 'media[]';
   newInput.className = 'box';
   newInput.accept = 'image/*,video/*';
   newInput.multiple = true;
   newInput.onchange = previewMedia;

   newInput.style.display = 'none';
   document.querySelector('.media-upload').appendChild(newInput);
   newInput.click(); // Simulate a click to open the file dialog
}

