<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('API Tokens') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Create New Token Form -->
                    <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Create New API Token</h3>
                        
                        <form id="createTokenForm" class="space-y-4">
                            @csrf
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Token Name</label>
                                <input type="text" id="name" name="name" required 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            
                            <div>
                                <label for="abilities" class="block text-sm font-medium text-gray-700">Abilities</label>
                                <select id="abilities" name="abilities[]" multiple 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="*" selected>All Permissions (*)</option>
                                    <option value="read">Read Only</option>
                                    <option value="write">Write</option>
                                    <option value="delete">Delete</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="expires_at" class="block text-sm font-medium text-gray-700">Expires At (Optional)</label>
                                <input type="datetime-local" id="expires_at" name="expires_at" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Create Token
                            </button>
                        </form>
                    </div>

                    <!-- Token List -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Your API Tokens</h3>
                        <div id="tokenList" class="space-y-4">
                            <!-- Tokens will be loaded here -->
                        </div>
                    </div>

                    <!-- New Token Display Modal -->
                    <div id="tokenModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
                        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                            <div class="mt-3 text-center">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">New API Token Created!</h3>
                                <div class="mt-2 px-7 py-3">
                                    <p class="text-sm text-gray-500 mb-4">Copy this token now. You won't be able to see it again!</p>
                                    <div class="bg-gray-100 p-3 rounded border">
                                        <code id="newToken" class="text-sm break-all"></code>
                                    </div>
                                </div>
                                <div class="items-center px-4 py-3">
                                    <button id="closeModal" 
                                            class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Load tokens on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadTokens();
        });

        // Create token form submission
        document.getElementById('createTokenForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const abilities = Array.from(document.getElementById('abilities').selectedOptions).map(option => option.value);
            
            fetch('/api-tokens', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    name: formData.get('name'),
                    abilities: abilities,
                    expires_at: formData.get('expires_at') || null
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data.token) {
                    showTokenModal(data.data.token);
                    this.reset();
                    loadTokens();
                } else {
                    alert('Error creating token: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating token');
            });
        });

        // Load tokens
        function loadTokens() {
            fetch('/api-tokens')
            .then(response => response.json())
            .then(data => {
                const tokenList = document.getElementById('tokenList');
                tokenList.innerHTML = '';
                
                if (data.data && data.data.length > 0) {
                    data.data.forEach(token => {
                        const tokenElement = createTokenElement(token);
                        tokenList.appendChild(tokenElement);
                    });
                } else {
                    tokenList.innerHTML = '<p class="text-gray-500">No API tokens found.</p>';
                }
            })
            .catch(error => {
                console.error('Error loading tokens:', error);
            });
        }

        // Create token element
        function createTokenElement(token) {
            const div = document.createElement('div');
            div.className = 'border rounded-lg p-4 bg-white shadow-sm';
            
            const isExpired = token.expires_at && new Date(token.expires_at) < new Date();
            const statusClass = isExpired ? 'text-red-600' : 'text-green-600';
            const statusText = isExpired ? 'Expired' : 'Active';
            
            div.innerHTML = `
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900">${token.name}</h4>
                        <p class="text-sm text-gray-500 mt-1">
                            Abilities: ${token.abilities.join(', ')}
                        </p>
                        <p class="text-sm text-gray-500">
                            Created: ${new Date(token.created_at).toLocaleDateString()}
                        </p>
                        ${token.expires_at ? `
                            <p class="text-sm text-gray-500">
                                Expires: ${new Date(token.expires_at).toLocaleDateString()}
                            </p>
                        ` : ''}
                        ${token.last_used_at ? `
                            <p class="text-sm text-gray-500">
                                Last used: ${new Date(token.last_used_at).toLocaleDateString()}
                            </p>
                        ` : ''}
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                            ${statusText}
                        </span>
                    </div>
                    <button onclick="deleteToken(${token.id})" 
                            class="ml-4 px-3 py-1 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded">
                        Delete
                    </button>
                </div>
            `;
            
            return div;
        }

        // Delete token
        function deleteToken(tokenId) {
            if (confirm('Are you sure you want to delete this token?')) {
                fetch(`/api-tokens/${tokenId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    loadTokens();
                })
                .catch(error => {
                    console.error('Error deleting token:', error);
                    alert('Error deleting token');
                });
            }
        }

        // Show token modal
        function showTokenModal(token) {
            document.getElementById('newToken').textContent = token;
            document.getElementById('tokenModal').classList.remove('hidden');
        }

        // Close modal
        document.getElementById('closeModal').addEventListener('click', function() {
            document.getElementById('tokenModal').classList.add('hidden');
        });

        // Close modal when clicking outside
        document.getElementById('tokenModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    </script>
</x-app-layout> 