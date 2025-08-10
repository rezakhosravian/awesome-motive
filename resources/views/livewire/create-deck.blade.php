<div class="max-w-2xl mx-auto">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <!-- Success Message -->
            @if($showSuccess)
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded animate-pulse">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ $successMessage }}
                    </div>
                </div>
            @endif

            <!-- Flash Error Message -->
            @if(session()->has('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <form wire:submit="save">
                <!-- Deck Name -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Deck Name *
                    </label>
                    <input 
                        wire:model.live="name"
                        id="name" 
                        type="text" 
                        placeholder="Enter deck name..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 focus:ring-red-500 @enderror">
                    
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 animate-bounce">{{ $message }}</p>
                    @enderror
                    
                    <!-- Real-time character count -->
                    <p class="mt-1 text-xs text-gray-500">
                        {{ strlen($name) }}/255 characters
                    </p>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea 
                        wire:model.live="description"
                        id="description" 
                        rows="4" 
                        placeholder="Describe what this deck is about..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 focus:ring-red-500 @enderror"></textarea>
                    
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 animate-bounce">{{ $message }}</p>
                    @enderror
                    
                    <!-- Real-time character count -->
                    <p class="mt-1 text-xs text-gray-500">
                        {{ strlen($description) }}/1000 characters
                    </p>
                </div>

                <!-- Public Checkbox -->
                <div class="mb-6">
                    <div class="flex items-center">
                        <input 
                            wire:model.live="is_public"
                            id="is_public" 
                            type="checkbox" 
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_public" class="ml-2 block text-sm text-gray-700">
                            Make this deck public
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Public decks can be viewed by other users and accessed via the API
                    </p>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3">
                    <button 
                        type="button"
                        wire:click="resetForm"
                        class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-4 py-2 rounded-lg font-medium transition-colors shadow-sm">
                        Clear Form
                    </button>
                    
                    <a href="{{ route('decks.index') }}" 
                       wire:navigate
                       class="bg-gray-500 hover:bg-gray-600 text-white border border-gray-500 hover:border-gray-600 px-4 py-2 rounded-lg font-medium transition-colors shadow-sm inline-block">
                        Cancel
                    </a>
                    
                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="bg-blue-600 hover:bg-blue-700 text-white border border-blue-600 hover:border-blue-700 px-4 py-2 rounded-lg font-medium transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        
                        <span wire:loading.remove wire:target="save">
                            Create Deck
                        </span>
                        
                        <span wire:loading wire:target="save" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating...
                        </span>
                    </button>
                </div>
            </form>

            <!-- Form Preview (when data is entered) -->
            @if($name || $description)
                <div class="mt-8 p-4 bg-gray-50 rounded-lg border">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Preview:</h4>
                    <div class="text-sm">
                        <p><strong>Name:</strong> {{ $name ?: 'Untitled Deck' }}</p>
                        @if($description)
                            <p><strong>Description:</strong> {{ $description }}</p>
                        @endif
                        <p><strong>Visibility:</strong> 
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $is_public ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $is_public ? 'Public' : 'Private' }}
                            </span>
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
