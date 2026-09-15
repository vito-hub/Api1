@extends('layouts.app')

@section('content')

    <section class="min-h-screen bg-gray-50 dark:bg-gray-900">

        <div class="mx-auto flex min-h-screen w-full max-w-md flex-col items-center justify-center px-4 py-8">
            {{-- Logo --}}
            <div class="mb-10 flex flex-col items-center text-center">

                <a href="/" class="mb-6">
                    <img
                        src="https://liangroup.net/media/logos/logo-fa.webp"
                        alt="Lian"
                        class="h-auto w-32"
                    >
                </a>

                <h1 dir="rtl" class="text-xl font-bold text-gray-800 dark:text-white">
                    به لیان خوش آمدید
                </h1>

                <h2 dir="rtl" class="mt-2 text-base font-medium leading-7 text-gray-600 dark:text-gray-300">
                    تا تخصص راه زیادی در پیش نیست<br>
                    با ما همراه شوید
                </h2>

            </div>
            {{-- Login Card --}}
            <div class="w-full rounded-lg border border-gray-200 bg-white shadow
                        dark:border-gray-700 dark:bg-gray-800">

                <div class="p-6 sm:p-8">

                    <form
                        method="POST"
                        action="{{ route('verify-password') }}"
                        class="space-y-6"
                    >
                        @csrf

                        <div>
                            <label dir="rtl" for="phone" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white"
                            >
                                enter your password:
                            </label>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                placeholder="09121234567"
                                required
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5
                                       text-sm text-gray-900 outline-none
                                       focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                                       dark:border-gray-600 dark:bg-gray-700 dark:text-white
                                       dark:placeholder-gray-400"
                            >
                            @error('password')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-lg bg-blue-600 px-5 py-2.5
                                   text-sm font-medium text-white
                                   hover:bg-blue-700
                                   focus:outline-none focus:ring-4 focus:ring-blue-300
                                   dark:bg-blue-600 dark:hover:bg-blue-700
                                   dark:focus:ring-blue-800"
                        >
                            ارسال
                        </button>

                    </form>

                </div>
            </div>

        </div>

    </section>

@endsection
