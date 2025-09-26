@if (isset($message))
    <div class="relative px-4 py-3 leading-normal text-blue-700 bg-blue-100 rounded-lg" role="alert">
        <strong class="font-bold">Info!</strong>
        <span class="block sm:inline">{{ $message }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
            <svg class="h-6 w-6 text-blue-700" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" onclick="this.parentNode.parentNode.style.display='none'">
                <title>Close</title>
                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 2.65a1.2 1.2 0 1 1-1.697-1.697l2.758-2.758-2.758-2.758a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-2.651a1.2 1.2 0 1 1 1.697 1.697l-2.758 2.758 2.758 2.758a1.2 1.2 0 0 1 0 1.697z"/>
            </svg>
        </span>
    </div>
@endif

@if (isset($error))
    {{-- Using a slightly different style for errors, typically red --}}
    <div class="relative px-4 py-3 leading-normal text-red-700 bg-red-100 rounded-lg" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{{ $error }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
            <svg class="h-6 w-6 text-red-700" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" onclick="this.parentNode.parentNode.style.display='none'">
                <title>Close</title>
                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 2.65a1.2 1.2 0 1 1-1.697-1.697l2.758-2.758-2.758-2.758a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-2.651a1.2 1.2 0 1 1 1.697 1.697l-2.758 2.758 2.758 2.758a1.2 1.2 0 0 1 0 1.697z"/>
            </svg>
        </span>
    </div>
@endif

@if($errors->any())
    {{-- Using a slightly different style for validation errors, typically orange/yellow --}}
    <div class="relative px-4 py-3 leading-normal text-orange-700 bg-orange-100 rounded-lg" role="alert">
        <strong class="font-bold">Validation Error!</strong>
        <span class="block sm:inline">{{ $errors->first() }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
            <svg class="h-6 w-6 text-orange-700" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" onclick="this.parentNode.parentNode.style.display='none'">
                <title>Close</title>
                <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 2.65a1.2 1.2 0 1 1-1.697-1.697l2.758-2.758-2.758-2.758a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-2.651a1.2 1.2 0 1 1 1.697 1.697l-2.758 2.758 2.758 2.758a1.2 1.2 0 0 1 0 1.697z"/>
            </svg>
        </span>
    </div>
@endif
