$(document).ready(function(){

    // Toggle nav menu
    $('.navbar-toggler').click(function(){
        $('#navbarNav').toggle();
    });


    $(document).click(function(){
        $('.edit-menu').addClass('d-none')
        $('.edit-menu').empty();
    });


})
// toggle post edit btn
function clickEdit(type, postId, e){
    e.stopPropagation();
    let editBtn = $(`.edit-menu_${postId}`);
    let editCmnt = $(`.edit-cmnt_${postId}`);
    if(type == "comment") {
        if(editCmnt.hasClass('d-none')){
            editCmnt.toggleClass('d-none');
            editCmnt.append(
            `<a href="/comment/edit/${postId}" class="text-warning d-block text-decoration-none mb-2">Edit</a>
             <a href="/comment/delete/${postId}" class="text-danger d-block text-decoration-none">Delete</a>`);
        }else{
            editCmnt.toggleClass('d-none');
            editCmnt.empty();
        }
    }else{
        if(editBtn.hasClass('d-none')){
            editBtn.toggleClass('d-none');
            editBtn.append(
            `<a href="/edit/${postId}" class="text-warning d-block text-decoration-none mb-2">Edit</a>
             <a href="/delete/${postId}" class="text-danger d-block text-decoration-none">Delete</a>`);
        }else{
            editBtn.toggleClass('d-none');
            editBtn.empty();
        }
    }
}