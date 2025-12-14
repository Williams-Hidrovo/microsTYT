namespace AuthService.Application.Interfaces;

public interface IJwtTokenGenerator
{
    string GenerateToken(Guid userId, string email);
}
