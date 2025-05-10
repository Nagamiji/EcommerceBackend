@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Products</h3>
            <button class="btn btn-primary float-right" onclick="showAddProductModal()">Add Product</button>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="products-table">
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div class="modal fade" id="productModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal-title">Add Product</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="product-form">
                        <input type="hidden" id="product-id">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" class="form-control" id="price" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="stock">Stock</label>
                            <input type="number" class="form-control" id="stock" required>
                        </div>
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select class="form-control" id="category_id" required>
                                <option value="">Select Category</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="is_public">Public</label>
                            <input type="checkbox" id="is_public">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="saveProduct()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = '{{ route('login') }}';
        }

        // Fetch Categories for Dropdown
        fetch('/api/categories', {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('category_id');
            data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                select.appendChild(option);
            });
        });

        // Fetch Products
        function loadProducts() {
            fetch('/api/products', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('products-table');
                tbody.innerHTML = '';
                data.forEach(product => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${product.id}</td>
                        <td>${product.name}</td>
                        <td>${product.price}</td>
                        <td>${product.stock}</td>
                        <td>${product.category ? product.category.name : 'N/A'}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editProduct(${product.id})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteProduct(${product.id})">Delete</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            });
        }

        // Show Add Product Modal
        function showAddProductModal() {
            document.getElementById('product-form').reset();
            document.getElementById('product-id').value = '';
            document.getElementById('modal-title').textContent = 'Add Product';
            $('#productModal').modal('show');
        }

        // Edit Product
        function editProduct(id) {
            fetch(`/api/products/${id}`, {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            })
            .then(response => response.json())
            .then(product => {
                document.getElementById('product-id').value = product.id;
                document.getElementById('name').value = product.name;
                document.getElementById('description').value = product.description || '';
                document.getElementById('price').value = product.price;
                document.getElementById('stock').value = product.stock;
                document.getElementById('category_id').value = product.category_id;
                document.getElementById('is_public').checked = product.is_public;
                document.getElementById('modal-title').textContent = 'Edit Product';
                $('#productModal').modal('show');
            });
        }

        // Save Product
        function saveProduct() {
            const id = document.getElementById('product-id').value;
            const product = {
                name: document.getElementById('name').value,
                description: document.getElementById('description').value,
                price: parseFloat(document.getElementById('price').value),
                stock: parseInt(document.getElementById('stock').value),
                category_id: parseInt(document.getElementById('category_id').value),
                is_public: document.getElementById('is_public').checked,
            };

            const method = id ? 'PUT' : 'POST';
            const url = id ? `/api/products/${id}` : '/api/products';

            fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(product)
            })
            .then(response => {
                if (!response.ok) throw new Error('Failed to save product');
                $('#productModal').modal('hide');
                loadProducts();
            })
            .catch(error => alert(error.message));
        }

        // Delete Product
        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                fetch(`/api/products/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to delete product');
                    loadProducts();
                })
                .catch(error => alert(error.message));
            }
        }

        // Load Products on Page Load
        loadProducts();
    </script>
@endsection