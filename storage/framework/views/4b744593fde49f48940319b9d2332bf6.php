

<?php $__env->startSection('title', 'Categories'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Categories</h1>
                </div>
                <div class="col-sm-6">
                    <button id="add-category-btn" class="btn btn-primary float-right">Add New Category</button>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php if(session('success')): ?>
                <div class="alert alert-success">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="alert alert-danger">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <input type="text" id="search" class="form-control" placeholder="Search categories by name...">
                    </div>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Products</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="categories-table"></tbody>
                    </table>
                    <div id="pagination" class="mt-3"></div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">Add Category</h5>
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">
                <form id="category-form">
                    <input type="hidden" id="category-id">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required maxlength="255">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" maxlength="1000"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveCategory()">Save</button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    let currentPage = 1;
    let perPage = 10;
    const baseUrl = 'http://127.0.0.1:8000';

    // Log initial cookies for debugging
    console.log('Initial cookies:', document.cookie);

    // Initialize CSRF token
    console.log('Fetching CSRF token from /sanctum/csrf-cookie');
    fetch(`${baseUrl}/sanctum/csrf-cookie`, { 
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            console.log('CSRF token fetch response:', response.status, response.statusText);
            console.log('Cookies after CSRF fetch:', document.cookie);
            if (response.status !== 204) {
                throw new Error(`Failed to fetch CSRF token: ${response.status} - ${response.statusText}`);
            }
            return response;
        })
        .then(() => loadCategories())
        .catch(error => {
            console.error('Error fetching CSRF token:', error);
            alert('Failed to initialize session: ' + error.message);
            window.location.href = '<?php echo e(route('login')); ?>';
        });

    // Load Categories
    function loadCategories(search = '') {
        const url = `${baseUrl}/api/categories?per_page=${perPage}&page=${currentPage}` + (search ? `&search=${encodeURIComponent(search)}` : '');
        console.log('Fetching categories:', url);
        console.log('CSRF token for request:', document.querySelector('meta[name="csrf-token"]').content);
        fetch(url, {
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                console.log('Categories response status:', response.status, response.statusText);
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Response text:', text);
                        throw new Error(`HTTP error: ${response.status} - ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Categories data:', data);
                const tbody = document.getElementById('categories-table');
                tbody.innerHTML = '';
                if (data.data && Array.isArray(data.data)) {
                    data.data.forEach(category => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${category.id}</td>
                            <td>${category.name}</td>
                            <td>${category.description || 'None'}</td>
                            <td>${category.products_count || 0}</td>
                            <td>
                                <a href="/admin/categories/${category.id}" class="btn btn-info btn-sm">View</a>
                                <button class="btn btn-warning btn-sm" onclick="editCategory(${category.id})">Edit</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteCategory(${category.id})">Delete</button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    renderPagination(data.meta);
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center">No categories available.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading categories:', error);
                alert('Failed to load categories: ' + error.message);
                document.getElementById('categories-table').innerHTML = '<tr><td colspan="5" class="text-center">Error loading categories: ' + error.message + '</td></tr>';
            });
    }

    // Render Pagination
    function renderPagination(meta) {
        console.log('Rendering pagination:', meta);
        const pagination = document.getElementById('pagination');
        pagination.innerHTML = `
            <nav>
                <ul class="pagination">
                    <li class="page-item ${meta.current_page === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(1)">First</a>
                    </li>
                    <li class="page-item ${meta.current_page === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${meta.current_page - 1})">Previous</a>
                    </li>
                    <li class="page-item ${meta.current_page === meta.last_page ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${meta.current_page + 1})">Next</a>
                    </li>
                    <li class="page-item ${meta.current_page === meta.last_page ? 'disabled' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${meta.last_page})">Last</a>
                    </li>
                </ul>
            </nav>
        `;
        pagination.dataset.lastPage = meta.last_page;
    }

    // Change Page
    function changePage(page) {
        if (page < 1 || page > parseInt(document.getElementById('pagination').dataset.lastPage)) return;
        currentPage = page;
        loadCategories(document.getElementById('search').value);
    }

    // Search Categories
    document.getElementById('search').addEventListener('input', function() {
        currentPage = 1;
        loadCategories(this.value);
    });

    // Show Add Category Modal
    function showAddCategoryModal() {
        document.getElementById('category-form').reset();
        document.getElementById('category-id').value = '';
        document.getElementById('modal-title').textContent = 'Add Category';
        $('#categoryModal').modal('show');
    }

    // Attach Event Listener for Add Category Button
    document.getElementById('add-category-btn').addEventListener('click', showAddCategoryModal);

    // Edit Category
    function editCategory(id) {
        console.log('Editing category:', id);
        fetch(`${baseUrl}/api/categories/${id}`, {
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                console.log('Edit category response:', response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP error: ${response.status} - ${text}`);
                    });
                }
                return response.json();
            })
            .then(response => {
                console.log('Edit category data:', response);
                const category = response.data;
                document.getElementById('category-id').value = category.id;
                document.getElementById('name').value = category.name;
                document.getElementById('description').value = category.description || '';
                document.getElementById('modal-title').textContent = 'Edit Category';
                $('#categoryModal').modal('show');
            })
            .catch(error => {
                console.error('Error editing category:', error);
                alert('Failed to load category: ' + error.message);
            });
    }

    // Save Category
    function saveCategory() {
        const id = document.getElementById('category-id').value;
        const data = {
            name: document.getElementById('name').value,
            description: document.getElementById('description').value || null,
        };
        console.log('Saving category:', id ? 'Update' : 'Create', data);

        fetch(id ? `${baseUrl}/api/categories/${id}` : `${baseUrl}/api/categories`, {
            method: id ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include',
            body: JSON.stringify(data),
        })
            .then(response => {
                console.log('Save category response:', response.status);
                if (!response.ok) {
                    return response.json().then(error => {
                        throw new Error(error.message || `HTTP error: ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Save category success:', data);
                $('#categoryModal').modal('hide');
                loadCategories();
                alert(data.message || 'Category saved successfully');
            })
            .catch(error => {
                console.error('Error saving category:', error);
                alert('Error: ' + error.message);
            });
    }

    // Delete Category
    function deleteCategory(id) {
        if (confirm('Are you sure you want to delete this category?')) {
            console.log('Deleting category:', id);
            fetch(`${baseUrl}/api/categories/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include',
            })
            .then(response => {
                console.log('Delete category response:', response.status);
                if (!response.ok) {
                    return response.json().then(error => {
                        throw new Error(error.message || `HTTP error: ${response.status}`);
                    });
                }
                loadCategories();
                alert('Category deleted successfully');
            })
            .catch(error => {
                console.error('Error deleting category:', error);
                alert('Error: ' + error.message);
            });
        }
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Year 4\S2\E-COMMERCE\backend1\resources\views/admin/categories/index.blade.php ENDPATH**/ ?>