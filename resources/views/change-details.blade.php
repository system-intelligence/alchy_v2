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