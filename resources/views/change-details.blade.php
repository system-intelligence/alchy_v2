<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Details Interface</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .highlight-added { background-color: rgba(34, 197, 94, 0.1); border-left: 4px solid #22c55e; }
        .highlight-removed { background-color: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; }
        .highlight-modified { background-color: rgba(251, 191, 36, 0.1); border-left: 4px solid #fbbf24; }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">History</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">View the history of changes</p>
        </div>

        <!-- View Changes Button -->
        <div class="mb-4">
            <button id="viewChangesBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                View Changes
            </button>
        </div>

        <!-- Changes Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Field</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Before</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">After</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">Field 1</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Value A</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">Value A'</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">Field 2</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Value B</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">Value B'</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">Field 3</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Value C</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">Value C'</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">Field 4</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Value D</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">Value D'</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">Field 5</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Value E</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">Value E'</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="changesModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detailed Changes</h3>
                    <button id="closeModalBtn" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Before Changes Panel -->
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-red-500 rounded-full p-2 mr-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-red-900 dark:text-red-100">Before Changes</h2>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Field 1</label>
                                <div class="text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded">Value A</div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Field 2</label>
                                <div class="text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded">Value B</div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Field 3</label>
                                <div class="text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded">Value C</div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Field 4</label>
                                <div class="text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded">Value D</div>
            Fields Changed                </div>

                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Field 5</label>
                                <div class="text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded">Value E</div>
                            </div>
                        </div>
                    </div>

                    <!-- After Changes Panel -->
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-green-500 rounded-full p-2 mr-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-green-900 dark:text-green-100">After Changes</h2>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border-l-4 border-green-500">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Field 1</label>
                                <div class="text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded font-semibold text-green-700 dark:text-green-300">Value A'</div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border-l-4 border-green-500">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Field 2</label>
                                <div class="text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded font-semibold text-green-700 dark:text-green-300">Value B'</div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border-l-4 border-green-500">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Field 3</label>
                                <div class="text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded font-semibold text-green-700 dark:text-green-300">Value C'</div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border-l-4 border-green-500">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Field 4</label>
                                <div class="text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded font-semibold text-green-700 dark:text-green-300">Value D'</div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border-l-4 border-green-500">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Field 5</label>
                                <div class="text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded font-semibold text-green-700 dark:text-green-300">Value E'</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Change Summary</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">3</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Items Added</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600 dark:text-red-400">1</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Items Removed</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">2</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Items Modified</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('changesModal');
        const btn = document.getElementById('viewChangesBtn');
        const closeBtn = document.getElementById('closeModalBtn');

        btn.onclick = function() {
            modal.classList.remove('hidden');
        }

        closeBtn.onclick = function() {
            modal.classList.add('hidden');
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.classList.add('hidden');
            }
        }
    </script>
</body>
</html>