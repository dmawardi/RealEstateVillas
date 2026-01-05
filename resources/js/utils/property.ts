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


export { calculateRates };