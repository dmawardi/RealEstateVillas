/**
 * Booking status utility functions
 */
const getStatusClass = (status: string): string => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        confirmed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        blocked: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        withdrawn: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    };
    return classes[status as keyof typeof classes] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
};

/**
 * Booking source utility functions
 */
const getSourceClass = (source: string): string => {
    const classes = {
        direct: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        airbnb: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        booking_com: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
        agoda: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        owner_blocked: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        maintenance: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        other: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    };
    return classes[source as keyof typeof classes] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
};

/**
 * Get booking status icon
 */
const getStatusIcon = (status: string): string => {
    const icons = {
        pending: '⏳',
        confirmed: '✅',
        cancelled: '❌',
        withdrawn: '↩️',
        completed: '🏁',
        blocked: '🚫',
    };
    return icons[status as keyof typeof icons] || '📋';
};

/**
 * Get booking source icon
 */
const getSourceIcon = (source: string): string => {
    const icons = {
        direct: '🏠',
        airbnb: '🏡',
        booking_com: '🏨',
        agoda: '🛏️',
        owner_blocked: '🔒',
        maintenance: '🔧',
        other: '📝',
    };
    return icons[source as keyof typeof icons] || '📋';
};

/**
 * Enhanced status utility with variants for different contexts
 */
const getStatusClassVariant = (status: string, variant: 'default' | 'light' | 'solid' = 'default'): string => {
    const statusColors = {
        pending: { bg: 'yellow', text: 'yellow' },
        confirmed: { bg: 'green', text: 'green' },
        cancelled: { bg: 'red', text: 'red' },
        completed: { bg: 'blue', text: 'blue' },
        blocked: { bg: 'gray', text: 'gray' },
        withdrawn: { bg: 'purple', text: 'purple' },
    };
    
    const color = statusColors[status as keyof typeof statusColors] || { bg: 'gray', text: 'gray' };
    
    switch (variant) {
        case 'light':
            return `bg-${color.bg}-50 text-${color.text}-700 dark:bg-${color.bg}-900/10 dark:text-${color.text}-300`;
        case 'solid':
            return `bg-${color.bg}-600 text-white dark:bg-${color.bg}-700`;
        default:
            return `bg-${color.bg}-100 text-${color.text}-800 dark:bg-${color.bg}-900 dark:text-${color.text}-200`;
    }
};

/**
 * Enhanced source utility with variants
 */
const getSourceClassVariant = (source: string, variant: 'default' | 'light' | 'solid' = 'default'): string => {
    const sourceColors = {
        direct: { bg: 'blue', text: 'blue' },
        airbnb: { bg: 'red', text: 'red' },
        booking_com: { bg: 'indigo', text: 'indigo' },
        agoda: { bg: 'orange', text: 'orange' },
        owner_blocked: { bg: 'gray', text: 'gray' },
        maintenance: { bg: 'yellow', text: 'yellow' },
        other: { bg: 'purple', text: 'purple' },
    };
    
    const color = sourceColors[source as keyof typeof sourceColors] || { bg: 'gray', text: 'gray' };
    
    switch (variant) {
        case 'light':
            return `bg-${color.bg}-50 text-${color.text}-700 dark:bg-${color.bg}-900/10 dark:text-${color.text}-300`;
        case 'solid':
            return `bg-${color.bg}-600 text-white dark:bg-${color.bg}-700`;
        default:
            return `bg-${color.bg}-100 text-${color.text}-800 dark:bg-${color.bg}-900 dark:text-${color.text}-200`;
    }
};

export { getStatusClass, getSourceClass, getStatusIcon, getSourceIcon, getStatusClassVariant, getSourceClassVariant };