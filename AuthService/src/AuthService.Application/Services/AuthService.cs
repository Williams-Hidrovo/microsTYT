using System.Security.Cryptography;
using AuthService.Application.DTOs;
using AuthService.Application.Interfaces;
using AuthService.Domain.Entities;
using Microsoft.EntityFrameworkCore;

namespace AuthService.Application.Services;

public class AuthService : IAuthService
{
    private readonly IAuthDbContext _context;
    private readonly IJwtTokenGenerator _jwtTokenGenerator;

    public AuthService(IAuthDbContext context, IJwtTokenGenerator jwtTokenGenerator)
    {
        _context = context;
        _jwtTokenGenerator = jwtTokenGenerator;
    }

    public async Task<AuthResponse> RegisterAsync(RegisterRequest request)
    {
        if (request.Password != request.ConfirmPassword)
            throw new Exception("Las contraseñas no coinciden");

        var existingUser = await _context.Users
            .FirstOrDefaultAsync(u => u.Email == request.Email);

        if (existingUser != null)
            throw new Exception("El email ya está registrado");

        var passwordHash = BCrypt.Net.BCrypt.HashPassword(request.Password);
        var user = new User(request.Email, passwordHash);

        _context.Users.Add(user);
        await _context.SaveChangesAsync();

        return await GenerateAuthResponseAsync(user);
    }

    public async Task<AuthResponse> LoginAsync(LoginRequest request)
    {
        var user = await _context.Users
            .FirstOrDefaultAsync(u => u.Email == request.Email);

        if (user == null || !BCrypt.Net.BCrypt.Verify(request.Password, user.PasswordHash))
            throw new Exception("Email o contraseña incorrectos");

        if (!user.IsActive)
            throw new Exception("La cuenta está desactivada");

        return await GenerateAuthResponseAsync(user);
    }

    public async Task<AuthResponse> RefreshTokenAsync(string refreshToken)
    {
        var token = await _context.RefreshTokens
            .Include(rt => rt.User)
            .FirstOrDefaultAsync(rt => rt.Token == refreshToken);

        if (token == null || token.IsRevoked || token.ExpiresAt < DateTime.UtcNow)
            throw new Exception("Token de actualización inválido o expirado");

        token.Revoke();
        await _context.SaveChangesAsync();

        return await GenerateAuthResponseAsync(token.User);
    }

    public async Task RevokeTokenAsync(string refreshToken)
    {
        var token = await _context.RefreshTokens
            .FirstOrDefaultAsync(rt => rt.Token == refreshToken);

        if (token == null)
            throw new Exception("Token no encontrado");

        token.Revoke();
        await _context.SaveChangesAsync();
    }

    private async Task<AuthResponse> GenerateAuthResponseAsync(User user)
    {
        var accessToken = _jwtTokenGenerator.GenerateToken(user.Id, user.Email);
        var refreshToken = GenerateRefreshToken();
        var expiresAt = DateTime.UtcNow.AddDays(7);

        var refreshTokenEntity = new RefreshToken(refreshToken, expiresAt, user.Id);
        _context.RefreshTokens.Add(refreshTokenEntity);
        await _context.SaveChangesAsync();

        return new AuthResponse(accessToken, refreshToken, expiresAt);
    }

    private static string GenerateRefreshToken()
    {
        var randomNumber = new byte[64];
        using var rng = RandomNumberGenerator.Create();
        rng.GetBytes(randomNumber);
        return Convert.ToBase64String(randomNumber);
    }
}
