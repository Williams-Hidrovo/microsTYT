using AuthService.Application.Interfaces;
using AuthService.Domain.Entities;
using Microsoft.EntityFrameworkCore;

namespace AuthService.Infrastructure.Persistence;

public class AuthDbContext : DbContext, IAuthDbContext
{
  public AuthDbContext(DbContextOptions<AuthDbContext> options)
      : base(options) { }

  public DbSet<User> Users => Set<User>();
  public DbSet<Role> Roles => Set<Role>();
  public DbSet<UserRole> UserRoles => Set<UserRole>();
  public DbSet<RefreshToken> RefreshTokens => Set<RefreshToken>();

  protected override void OnModelCreating(ModelBuilder modelBuilder)
  {
    modelBuilder.ApplyConfigurationsFromAssembly(typeof(AuthDbContext).Assembly);
  }
}
