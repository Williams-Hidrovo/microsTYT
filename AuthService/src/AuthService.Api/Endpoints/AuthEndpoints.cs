using AuthService.Application.DTOs;
using AuthService.Application.Services;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;

namespace AuthService.Api.Endpoints;

public static class AuthEndpoints
{
    public static void MapAuthEndpoints(this IEndpointRouteBuilder app)
    {
        var group = app.MapGroup("/api/auth").WithTags("Authentication");

        group.MapPost("/register", Register)
            .WithName("Register")
            .WithOpenApi();

        group.MapPost("/login", Login)
            .WithName("Login")
            .WithOpenApi();

        group.MapPost("/refresh", RefreshToken)
            .WithName("RefreshToken")
            .WithOpenApi();

        group.MapPost("/revoke", RevokeToken)
            .RequireAuthorization()
            .WithName("RevokeToken")
            .WithOpenApi();

        group.MapGet("/me", GetCurrentUser)
            .RequireAuthorization()
            .WithName("GetCurrentUser")
            .WithOpenApi();
    }

    private static async Task<IResult> Register(
        [FromBody] RegisterRequest request,
        [FromServices] IAuthService authService)
    {
        try
        {
            var response = await authService.RegisterAsync(request);
            return Results.Ok(response);
        }
        catch (Exception ex)
        {
            return Results.BadRequest(new { error = ex.Message });
        }
    }

    private static async Task<IResult> Login(
        [FromBody] LoginRequest request,
        [FromServices] IAuthService authService)
    {
        try
        {
            var response = await authService.LoginAsync(request);
            return Results.Ok(response);
        }
        catch (Exception ex)
        {
            return Results.BadRequest(new { error = ex.Message });
        }
    }

    private static async Task<IResult> RefreshToken(
        [FromBody] RefreshTokenRequest request,
        [FromServices] IAuthService authService)
    {
        try
        {
            var response = await authService.RefreshTokenAsync(request.RefreshToken);
            return Results.Ok(response);
        }
        catch (Exception ex)
        {
            return Results.BadRequest(new { error = ex.Message });
        }
    }

    private static async Task<IResult> RevokeToken(
        [FromBody] RefreshTokenRequest request,
        [FromServices] IAuthService authService)
    {
        try
        {
            await authService.RevokeTokenAsync(request.RefreshToken);
            return Results.Ok(new { message = "Token revocado exitosamente" });
        }
        catch (Exception ex)
        {
            return Results.BadRequest(new { error = ex.Message });
        }
    }

    private static IResult GetCurrentUser(HttpContext context)
    {
        var userId = context.User.FindFirst("sub")?.Value;
        var email = context.User.FindFirst("email")?.Value;

        if (userId == null || email == null)
        {
            return Results.Unauthorized();
        }

        return Results.Ok(new
        {
            userId,
            email,
            claims = context.User.Claims.Select(c => new { c.Type, c.Value })
        });
    }
}
