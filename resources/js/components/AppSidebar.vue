<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, LampCeiling, User, Heart } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';

// Grab inertia variables
const page = usePage();

// Determine if the user is an admin (Impacts what links are rendered)
const isAdmin = computed(() => {
    const user = (page.props as any).auth?.user;
    return user?.is_admin ?? user?.isAdmin ?? false;
});

const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'Users',
        href: route('admin.users.index'),
        icon: User,
    },
    {
        title: 'Properties',
        href: route('admin.properties.index'),
        icon: Folder,
    },
    {
        title: 'Property Features',
        href: route('admin.features.index'),
        icon: LampCeiling,
    },
    {
        title: 'Bookings',
        href: route('admin.bookings.index'),
        icon: BookOpen,
    },
    {
        title: 'My Favorites',
        href: route('my.favorites'),
        icon: Heart,
    }
];

const userNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'My Bookings',
        href: route('my.bookings'),
        icon: BookOpen,
    },
    {
        title: 'My Favorites',
        href: route('my.favorites'),
        icon: Heart,
    }
];

// const footerNavItems: NavItem[] = [
//     {
//         title: 'Github Repo',
//         href: 'https://github.com/laravel/vue-starter-kit',
//         icon: Folder,
//     },
//     {
//         title: 'Documentation',
//         href: 'https://laravel.com/docs/starter-kits#vue',
//         icon: BookOpen,
//     },
// ];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain v-if="isAdmin" :items="adminNavItems" />
            <NavMain v-else :items="userNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
