

// JavaScript for ranking.html

function create_box(object, index){
    return `
<li class="list-row">
    <div class="text-4xl font-thin opacity-30 tabular-nums">${index}</div>
    <div><img class="size-10 rounded-box" src="${object['image']}"/></div>
    <div class="list-col-grow">
        <div>${object['name']}</div>
        <div class="text-xs uppercase font-semibold opacity-60">${object['elo']}</div>
    </div>
</li>
`
}

const api = 'http://localhost:8000/ranking';
const container = document.getElementById('container');

fetch(api)
.then(response => response.json())
.then(data => {
    for (let index = 0; index < data.length; index++) {
        const element = data[index];
        container.innerHTML = container.innerHTML + create_box(element, index + 1);
    }
})