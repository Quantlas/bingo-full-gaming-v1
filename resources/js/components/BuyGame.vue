<script setup></script>

<template>
    <div
        id="popup-modal"
        tabindex="-1"
        class="fixed left-0 right-0 top-0 z-50 h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden md:inset-0"
    >
        <div class="relative max-h-full w-full max-w-md p-4">
            <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
                <button
                    type="button"
                    class="absolute end-2.5 top-3 ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white"
                    data-modal-hide="popup-modal"
                >
                    <svg class="h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
                        />
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="p-4 text-center md:p-5">
                    <div>
                        <h2 class="sr-only">Steps</h2>

                        <div
                            class="relative after:absolute after:inset-x-0 after:top-1/2 after:block after:h-0.5 after:-translate-y-1/2 after:rounded-lg after:bg-gray-100"
                        >
                            <ol class="relative z-10 flex justify-between text-sm font-medium text-gray-500">
                                <li class="flex items-center gap-2 bg-white p-2">
                                    <span class="size-6 rounded-full bg-gray-100 text-center text-[10px]/6 font-bold"> 1 </span>

                                    <span class="hidden sm:block"> Details </span>
                                </li>

                                <li class="flex items-center gap-2 bg-white p-2">
                                    <span class="size-6 rounded-full bg-blue-600 text-center text-[10px]/6 font-bold text-white"> 2 </span>

                                    <span class="hidden sm:block"> Address </span>
                                </li>

                                <li class="flex items-center gap-2 bg-white p-2">
                                    <span class="size-6 rounded-full bg-gray-100 text-center text-[10px]/6 font-bold"> 3 </span>

                                    <span class="hidden sm:block"> Payment </span>
                                </li>
                            </ol>
                        </div>
                    </div>
                    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this product?</h3>
                    <button
                        data-modal-hide="popup-modal"
                        type="button"
                        class="inline-flex items-center rounded-lg bg-red-600 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-red-800 focus:outline-none focus:ring-4 focus:ring-red-300 dark:focus:ring-red-800"
                    >
                        Yes, I'm sure
                    </button>
                    <button
                        data-modal-hide="popup-modal"
                        type="button"
                        class="ms-3 rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700"
                    >
                        No, cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
