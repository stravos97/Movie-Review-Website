const userss = document.getElementById('deleteUser')
console.log("ggg");

/**
 * Javascript is creating a fetch call to /userss/delete/$id
 * Its then going to get a response. When it does it's going to call the promise .then, then reload the page and the item should be deleted
 */

if (userss) {
    userss.addEventListener("click", (e) => {

        if (e.target.className === 'btn btn-danger delete-article border-r'){

            if (confirm('Are you sure?')){
                const id = e.target.getAttribute('data-id');
                console.log("ff");
                console.log(id);
                fetch(`userss/remove/${id}`, {
                    method: 'DELETE'
                }).then(res => window.location.reload());
            }

        }

    })
}