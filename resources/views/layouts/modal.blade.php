<!-- Delete Modal -->
    <div id="deleteModal" class="delete-modal">
        <div class="delete-box">
            <h5>Confirm Deletion</h5>
            <p>Are you sure you want to delete this shapefile?</p>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Restore Modal -->
    <div id="restoreModal" class="delete-modal">
        <div class="delete-box">
            <h5>Confirm Restore</h5>
            <p>Are you sure you want to restore this shapefile?</p>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button class="btn btn-secondary" onclick="closeRestoreModal()">Cancel</button>
                <form id="restoreForm" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">Restore</button>
                </form>
            </div>
        </div>
    </div>