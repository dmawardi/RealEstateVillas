import { Car, GraduationCap, ShoppingBag, Trees, Heart, MapPin } from "lucide-vue-next";

const getAmenityIcon = (type: string) => {
    const icons = {
        'schools_nearby': GraduationCap,
        'transport': Car,
        'shopping': ShoppingBag,
        'parks': Trees,
        'medical': Heart
    };
    return icons[type as keyof typeof icons] || MapPin;
};

const getAmenityLabel = (type: string) => {
    const labels = {
        'schools_nearby': 'Schools Nearby',
        'transport': 'Transport',
        'shopping': 'Shopping',
        'parks': 'Parks & Recreation',
        'medical': 'Medical Facilities'
    };
    return labels[type as keyof typeof labels] || type;
};

export { getAmenityIcon, getAmenityLabel };