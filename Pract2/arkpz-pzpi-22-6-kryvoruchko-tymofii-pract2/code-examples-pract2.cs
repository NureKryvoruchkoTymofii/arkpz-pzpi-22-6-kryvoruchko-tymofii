// До рефакторингу
public class BasicEmployee
{
    public string Name { get; set; }
    public decimal Salary { get; set; }
    public void DisplayInfo()
    {
        Console.WriteLine($"Employee: {Name}, Salary: {Salary}");
    }
}
public class FullTimeEmployee : BasicEmployee
{
// Ніяких нових методів або властивостей немає
}


// Після рефакторингу
public class Employee
{
    public string Name { get; set; }
    public decimal Salary { get; set; }
    public void DisplayInfo()
    {
        Console.WriteLine($"Employee: {Name}, Salary: {Salary}");
    }
}

//-----------------------------------------------
// До рефакторингу
public class User
{
    public string Name { get; set; }
    public string Email { get; set; }

    public virtual void DisplayInfo()
    {
        Console.WriteLine($"Name: {Name}, Email: {Email}");
    }
}

public class RegisteredUser : User
{
    public override void DisplayInfo()
    {
        Console.WriteLine($"Registered User -> Name: {Name}, Email: {Email}");
    }
}

public class AdminUser : RegisteredUser
{
    public string AdminRole { get; set; }

    public void DisplayAdminRole()
    {
        Console.WriteLine($"Admin Role: {AdminRole}");
    }
}

// Після рефакторингу
public class User
{
    public string Name { get; set; }
    public string Email { get; set; }

    public virtual void DisplayInfo()
    {
        Console.WriteLine($"Name: {Name}, Email: {Email}");
    }
}

public class AdminUser : User
{
    public string AdminRole { get; set; }

    public override void DisplayInfo()
    {
        Console.WriteLine($"Admin User -> Name: {Name}, Email: {Email}");
    }

    public void DisplayAdminRole()
    {
        Console.WriteLine($"Admin Role: {AdminRole}");
    }
}

//-------------------------------------------
// До рефакторингу
void SetValue(string name, int value)
{
    if (name.Equals("height"))
    {
        height = value;
        return;
    }

    if (name.Equals("width"))
    {
        width = value;
        return;
    }

    Assert.Fail();
}

// Після грефакторингу
void SetHeight(int arg)
{
    height = arg;
}

void SetWidth(int arg)
{
    width = arg;
}

