import { Property, PropertyPricing } from "@/types";
import { formatPrice } from "./formatters";

const calculateRates = (pricing: PropertyPricing) => {
    if (!pricing?.nightly_rate) return null;
    
    const nightlyRate = pricing.nightly_rate;
    
    // Calculate weekly rate
    let weeklyRate = nightlyRate * 7; // Default to 7 days at nightly rate
    let weeklyDiscount = 0;
    
    if (pricing.weekly_discount_active && pricing.weekly_discount_percent && pricing.weekly_discount_percent > 0) {
        weeklyDiscount = pricing.weekly_discount_percent;
        const daysForWeekly = pricing.min_days_for_weekly || 7;
        weeklyRate = Math.round(nightlyRate * daysForWeekly * (1 - weeklyDiscount / 100));
    }
    
    // Calculate monthly rate
    let monthlyRate = nightlyRate * 30; // Default to 30 days at nightly rate
    let monthlyDiscount = 0;
    
    if (pricing.monthly_discount_active && pricing.monthly_discount_percent && pricing.monthly_discount_percent > 0) {
        monthlyDiscount = pricing.monthly_discount_percent;
        const daysForMonthly = pricing.min_days_for_monthly || 30;
        monthlyRate = Math.round(nightlyRate * daysForMonthly * (1 - monthlyDiscount / 100));
    }
    
    return {
        nightly: nightlyRate,
        weekly: weeklyRate,
        monthly: monthlyRate,
        weeklyDiscount,
        monthlyDiscount,
        weekendPremium: pricing.weekend_premium_active ? (pricing.weekend_premium_percent || 0) : 0
    };
};

const getPriceDisplay = (property: Property) => {
    // For sale properties - use the single price from properties table
    if (property.listing_type === 'for_sale') {
        if (property.price) {
            return formatPrice(property.price);
        }
        return 'Price on Application';
    }
    
    // For rent properties - use pricing calculations
    if (property.listing_type === 'for_rent') {
        // Assume backend provides the current active pricing as the first item
        // or as a separate currentPricing property
        const currentPricing = property.pricing && property.pricing.length > 0 
            ? property.pricing[0] 
            : null;
        
        if (currentPricing) {
            const rates = calculateRates(currentPricing);
            
            if (rates) {
                // Primary display: nightly rate
                const nightlyDisplay = `${formatPrice(rates.nightly)}/night`;
                
                // Add weekly rate with discount info if greater discount
                if (rates.weeklyDiscount >= rates.monthlyDiscount) {
                    const weeklyDisplay = `${formatPrice(rates.weekly)}/week`;
                    return `<p>${nightlyDisplay} •</p><p>${weeklyDisplay}</p>`;
                }
                
                // Add monthly rate with discount info if greater discount
                if (rates.monthlyDiscount > rates.weeklyDiscount) {
                    const monthlyDisplay = `${formatPrice(rates.monthly)}/month`;
                    return `<p>${nightlyDisplay} •</p><p>${monthlyDisplay}</p>`;
                }
                
                // Show just nightly + monthly for context if no significant discounts
                if (rates.monthly && rates.monthly !== rates.nightly * 30) {
                    return `${nightlyDisplay} • ${formatPrice(rates.monthly)}/month`;
                }
                
                return nightlyDisplay;
            }
            
            // Fallback to stored rates if calculation fails
            if (currentPricing.nightly_rate) {
                return `${formatPrice(currentPricing.nightly_rate)}/night`;
            }
            if (currentPricing.weekly_rate) {
                return `${formatPrice(currentPricing.weekly_rate)}/week`;
            }
            if (currentPricing.monthly_rate) {
                return `${formatPrice(currentPricing.monthly_rate)}/month`;
            }
        }
        
        // No pricing found
        return 'Rental Rate on Application';
    }
    
    // For other listing types (sold, off_market)
    return 'Price on Application';
};


export { calculateRates, getPriceDisplay };