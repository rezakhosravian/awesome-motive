<div>
    <!-- Trigger Button -->
    <button 
        wire:click="openModal"
        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center shadow-sm border border-green-600 hover:border-green-700">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
        </svg>
        Quick Create Deck
    </button>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:keydown.escape="closeModal">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" wire:click="closeModal"></div>
            
            <!-- Modal Content -->
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl border">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">
                            Create New Deck
                        </h3>
                        <button 
                            wire:click="closeModal"
                            class="text-gray-400 hover:text-gray-600 focus:outline-none p-1 rounded-md hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Form -->
                    <form wire:submit="save" class="space-y-4">
                        <!-- Deck Name -->
                        <div>
                            <label for="modal-name" class="block text-sm font-medium text-gray-700 mb-1">
                                Deck Name *
                            </label>
                            <input 
                                wire:model.live="name"
                                id="modal-name" 
                                type="text" 
                                placeholder="Enter deck name..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 focus:ring-red-500 @enderror">
                            
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="modal-description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea 
                                wire:model.live="description"
                                id="modal-description" 
                                rows="3" 
                                placeholder="Describe what this deck is about..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 focus:ring-red-500 @enderror"></textarea>
                            
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Public Checkbox -->
                        <div>
                            <div class="flex items-center">
                                <input 
                                    wire:model.live="is_public"
                                    id="modal-is-public" 
                                    type="checkbox" 
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="modal-is-public" class="ml-2 block text-sm text-gray-700">
                                    Make this deck public
                                </label>
                            </div>
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                            <button 
                                type="button"
                                wire:click="closeModal"
                                class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 px-4 py-2 rounded-lg font-medium transition-colors shadow-sm">
                                Cancel
                            </button>
                            
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="save"
                                class="bg-blue-600 hover:bg-blue-700 text-white border border-blue-600 hover:border-blue-700 px-4 py-2 rounded-lg font-medium transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                
                                <span wire:loading.remove wire:target="save">
                                    Create Deck
                                </span>
                                
                                <span wire:loading wire:target="save" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Creating...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
