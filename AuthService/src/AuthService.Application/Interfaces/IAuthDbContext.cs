using AuthService.Domain.Entities;
using Microsoft.EntityFrameworkCore;

namespace AuthService.Application.Interfaces;

public interface IAuthDbContext
{
    DbSet<User> Users { get; }
    DbSet<Role> Roles { get; }
    DbSet<UserRole> UserRoles { get; }
    DbSet<RefreshToken> RefreshTokens { get; }

    Task<int> SaveChangesAsync(CancellationToken cancellationToken = default);
}
