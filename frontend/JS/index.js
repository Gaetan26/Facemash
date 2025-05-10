
// JavaScript for index.html

const api = {
  'start' : 'http://localhost:8000/start',
  'next': 'http://localhost:8000/next'
}

const image_left = document.getElementById('image-left');
const image_right = document.getElementById('image-right');

const name_left = document.getElementById('name-left');
const name_right = document.getElementById('name-right');

let process_done = false
let opponents = {
  'left' : null,
  'right' : null
}

image_left.onclick = () => {
  if (process_done) {
    
    process_done = false
    
    fetch(api.next + `?winner=${opponents['left']}&loser=${opponents['right']}`)
    .then(response => response.json())
    .then(data => {
      
      image_right.src = data['image'];
      name_right.innerText = data['name'];

      opponents['right'] = data['id'];

      process_done = true

    })
    .catch(error => {
      console.error('error :', error);
    })

  }
}

image_right.onclick = () => {
  
  process_done = false
  
  fetch(api.next + `?winner=${opponents['right']}&loser=${opponents['left']}`)
  .then(response => response.json())
  .then(data => {
    
    image_left.src = data['image'];
    name_left.innerText = data['name'];

    opponents['left'] = data['id'];

    process_done = true

  })
  .catch(error => {
    console.error('error :', error);
  })

}

fetch(api.start)
  .then(response => response.json())
  .then(data => {

    image_left.src = data[0]['image'];
    name_left.innerText = data[0]['name'];

    image_right.src = data[1]['image'];
    name_right.innerText = data[1]['name'];

    opponents['left'] = data[0]['id'];
    opponents['right'] = data[1]['id'];

    process_done = true;

  })
  .catch(error => {
    console.error('error :', error);
  })