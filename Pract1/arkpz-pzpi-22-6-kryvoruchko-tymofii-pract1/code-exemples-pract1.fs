// ---Рекомендація 1---

// Поганий приклад 
let x c = c + 2;

// Гарний приклад
let incrementTwo number = number + 2;



// ---Рекомендація 2---

// Поганий приклад
let calculateSquare y = y * y;
let calculateCube y = y * y * y; 

// Гарний приклад
let calculateSquareGood y = y * y;
let calculateCubeGood y = y * calculateSquareGood y;


// ---Рекомендація 3---

// ---Рекомендація 3.1---
// Поганий приклад
let area length width =
    length * length // Логіка не відповідає очікуванням

let perimeter length width =
    length + length + width + width // Heoптимальне обчислення

let rectangle Properties length width =
    let x = area length width
    let y = perimeter length width
    printfn "Area and Perimeter combined: %f" (x + y) // Hевiдповiдне поєднання


// Гарний приклад
let calculateArea length width = length * width // Чиста функція
let calculatePerimeter length width = 2.0 * (length + width) // Ефективна функція

let printRectangleProperties length width =
    printfn "Area: %f" (calculateArea length width)
    printfn "Perimeter: %f" (calculatePerimeter length width)

printRectangleProperties 10.0 5.0

// ---Рекомендація 3.2---

// Поганий приклад
type Rectangle() =
member this.Calculate(length: float, width: float) =   
    printfn "Area: %f" (length * length) // Лoriка помилкова
    printfn "Perimeter: %f" (length + length + width + width) // Неоптимально
    printfn "Total: %f" ((length * length) + (length + length + width + width)) // 3мiшування метрик

//Гарний приклад
type Rectangle(length: float, width: float) =
    member this.Area = length * width
    member this.Perimeter = 2.0 * (length + width)

    member this.PrintProperties() =
        printfn "Area: %f" this.Area
        printfn "Perimeter: %f" this.Perimeter

// Використання класу
let rect = Rectangle(10.0, 5.0)
rect.PrintProperties()