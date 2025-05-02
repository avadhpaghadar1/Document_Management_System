@props(['routeName'])
<div class="modal fade" id="deleteModel" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Delete</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure want to Delete?
            </div>
            <div class="modal-footer">
                <form method="POST" action="{{route($routeName,['id'=>0])}}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="delete">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>

function setDeleteId(id) {
    document.querySelector('#deleteModel input[name="delete"]').value = id;
}

</script>