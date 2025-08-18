import { Location } from '@/types';
function processLocations(locations: Location[]) {
    // Group by type and create comma-separated strings
    const grouped: Record<string, string[]> = {};
    
    locations.forEach(location => {
        let key = '';
        if (location.type === 'regency') {
            key = 'regencies';
        } else {
            key = `${location.type}s`;
        }
        // If the location is not already grouped, create a new array
        if (!grouped[key]) grouped[key] = [];
        // then add the location name to the array
        grouped[key].push(location.name);
    });
    
    // Convert arrays to comma-separated strings
    const result: Record<string, string> = {};
    Object.entries(grouped).forEach(([key, values]) => {
        result[key] = values.join(',');
    });
    
    return result;
}

export { processLocations };