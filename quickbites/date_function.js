let now = new Date();

console.log(now.getFullYear()); // Year (e.g 2024)
console.log(now.getMonth()); // Month (0 = jenuary, 11 = december)
console.log(now.getDate()); // Day of the month (1-31)
console.log(now.getDay()); // Day of the week (0 = sunday, 6 = saturday)
console.log(now.getHours()); // Hours (0-23)
console.log(now.getMinutes()); // Minutes (0-59)
console.log(now.getSeconds()); // Seconds (0-59)
console.log(now.getMilliseconds()); // Milliseconds (0.999)


//setting part of the data
let date = new Date();

date.setFullYear(2025); //change year
date.setMonth(5); //change to june (0-based index)
date.setDate(15); //change to 18th of the month
date.setHours(10, 30, 0, 0); //change time to 12:45:00.000

console.log(date); //updated date
