namespace AuthService.Domain.Entities;

public class Role
{
  public Guid Id { get; private set; }
  public string Name { get; private set; } = null!;

  protected Role() { }

  public Role(string name)
  {
    Id = Guid.NewGuid();
    Name = name;
  }
}
