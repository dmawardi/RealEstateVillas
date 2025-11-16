<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

</script>

<template>
    <nav class="flex items-center justify-between gap-4 sticky top-0 z-50 w-full bg-white/70 px-4 py-3 text-sm font-medium text-[#1b1b18] backdrop-blur-md dark:bg-[#1b1b18]/70 dark:text-[#EDEDEC] sm:px-6 lg:px-8">
        <div>
            <Link :href="route('home')">Home</Link>
        </div>
        <div>
            <template v-if="$page.props.auth.user">
                <Link
                    :href="route('dashboard')"
                    class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                    Dashboard
                </Link>
                <Link
                    :href="route('logout')"
                    method="post"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Log out
                </Link>
            </template>
            <template v-else>
                <Link
                    :href="route('login')"
                    class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                >
                    Log in
                </Link>
                <Link
                    :href="route('register')"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Register
                </Link>
            </template>
        </div>
    </nav>
</template>