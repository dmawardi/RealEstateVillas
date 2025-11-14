import { Property, PropertyPricing } from "@/types";
import { formatPrice } from "./formatters";

const calculateRates = (pricing: PropertyPricing) => {
    if (!pricing?.nightly_rate) return null;
    
    const nightlyRate = pricing.nightly_rate;
    
    // Calculate weekly rate
    let weeklyRate = pricing.weekly_rate;
    let weeklyDiscount = pricing.weekly_discount_percent || 0;
    
    if (!weeklyRate && weeklyDiscount > 0) {
        // Calculate weekly rate from nightly rate with discount
        const daysForWeekly = pricing.min_days_for_weekly || 7;
        weeklyRate = Math.round(nightlyRate * daysForWeekly * (1 - weeklyDiscount / 100));
    } else if (!weeklyRate) {
        // Default weekly rate without discount (7 days)
        weeklyRate = nightlyRate * 7;
        weeklyDiscount = 0;
    }
    
    // Calculate monthly rate
    let monthlyRate = pricing.monthly_rate;
    let monthlyDiscount = pricing.monthly_discount_percent || 0;
    
    if (!monthlyRate && monthlyDiscount > 0) {
        // Calculate monthly rate from nightly rate with discount
        const daysForMonthly = pricing.min_days_for_monthly || 30;
        monthlyRate = Math.round(nightlyRate * daysForMonthly * (1 - monthlyDiscount / 100));
    } else if (!monthlyRate) {
        // Default monthly rate without discount (30 days)
        monthlyRate = nightlyRate * 30;
        monthlyDiscount = 0;
    }
    
    return {
        nightly: nightlyRate,
        weekly: weeklyRate,
        monthly: monthlyRate,
        weeklyDiscount,
        monthlyDiscount
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