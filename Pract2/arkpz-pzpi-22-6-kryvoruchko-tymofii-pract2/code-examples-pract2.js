// До рефакторингу
class DiscountCalculator {
    calculateDiscount(price, customerType) {
        if (customerType === "regular") {
            return price * 0.9; // 10% знижка
        } else if (customerType === "premium") {
            return price * 0.8; // 20% знижка
        } else {
            return price; // Без знижки
        }
    }
}

// Використання
const calculator = new DiscountCalculator();

console.log(calculator.calculateDiscount(100, "regular")); // 90
console.log(calculator.calculateDiscount(100, "premium")); // 80
console.log(calculator.calculateDiscount(100, "other"));   // 100

// Після рефакторингу
class DiscountCalculator {
    calculateRegularDiscount(price) {
        return price * 0.9; // 10% знижка
    }

    calculatePremiumDiscount(price) {
        return price * 0.8; // 20% знижка
    }

    calculateNoDiscount(price) {
        return price; // Без знижки
    }
}

// Використання
const calculator = new DiscountCalculator();

console.log(calculator.calculateRegularDiscount(100)); // 90
console.log(calculator.calculatePremiumDiscount(100)); // 80
console.log(calculator.calculateNoDiscount(100));      // 100

//----------------------------------------------------

// До рефакторингу
const user = {
    firstName: "Tymofii",
    lastName: "Kryvoruchko",
    email: "tymofii.@example.com",
    isActive: true,
};

// Логіка обробки даних
function getFullName(user) {
    return `${user.firstName} ${user.lastName}`;
}

function deactivateUser(user) {
    user.isActive = false;
}

// Використання
console.log(getFullName(user));
deactivateUser(user);
console.log(user.isActive); // false

// Після рефакторингу
class User {
    constructor(firstName, lastName, email, isActive = true) {
        this.firstName = firstName;
        this.lastName = lastName;
        this.email = email;
        this.isActive = isActive;
    }

    getFullName() {
        return `${this.firstName} ${this.lastName}`;
    }

    deactivate() {
        this.isActive = false;
    }
}

// Використання
const user = new User("John", "Doe", "john.doe@example.com");
console.log(user.getFullName()); // John Doe
user.deactivate();
console.log(user.isActive); // false

//----------------------------------------------

