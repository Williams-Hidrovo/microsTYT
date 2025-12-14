using System.Text;
using AuthService.Api.Endpoints;
using AuthService.Application.Interfaces;
using AuthService.Application.Services;
using AuthService.Infrastructure.Auth;
using AuthService.Infrastructure.Persistence;
using Microsoft.AspNetCore.Authentication.JwtBearer;
using Microsoft.EntityFrameworkCore;
using Microsoft.IdentityModel.Tokens;
using Microsoft.OpenApi.Models;

var builder = WebApplication.CreateBuilder(args);

builder.Services.AddDbContext<AuthDbContext>(options =>
    options.UseSqlServer(
        builder.Configuration.GetConnectionString("AuthDb")
    )
);

builder.Services.AddScoped<IAuthDbContext>(provider =>
    provider.GetRequiredService<AuthDbContext>());

var jwtSettings = builder.Configuration.GetSection("JwtSettings");
var jwtOptions = new JwtOptions
{
    Secret = jwtSettings["Secret"]!,
    Issuer = jwtSettings["Issuer"]!,
    Audience = jwtSettings["Audience"]!,
    ExpirationMinutes = int.Parse(jwtSettings["ExpirationMinutes"]!)
};

builder.Services.AddSingleton(jwtOptions);
builder.Services.AddSingleton<IJwtTokenGenerator, JwtTokenGenerator>();
builder.Services.AddScoped<IAuthService, AuthService.Application.Services.AuthService>();

builder.Services.AddAuthentication(JwtBearerDefaults.AuthenticationScheme)
    .AddJwtBearer(options =>
    {
        options.TokenValidationParameters = new TokenValidationParameters
        {
            ValidateIssuer = true,
            ValidateAudience = true,
            ValidateLifetime = true,
            ValidateIssuerSigningKey = true,
            ValidIssuer = jwtOptions.Issuer,
            ValidAudience = jwtOptions.Audience,
            IssuerSigningKey = new SymmetricSecurityKey(Encoding.UTF8.GetBytes(jwtOptions.Secret))
        };
    });

builder.Services.AddAuthorization();

builder.Services.AddEndpointsApiExplorer();
builder.Services.AddSwaggerGen(c =>
{
    c.SwaggerDoc("v1", new OpenApiInfo { Title = "Auth Service API", Version = "v1" });

    c.AddSecurityDefinition("Bearer", new OpenApiSecurityScheme
    {
        Description = "JWT Authorization header usando el esquema Bearer. Ejemplo: \"Bearer {token}\"",
        Name = "Authorization",
        In = ParameterLocation.Header,
        Type = SecuritySchemeType.ApiKey,
        Scheme = "Bearer"
    });

    c.AddSecurityRequirement(new OpenApiSecurityRequirement
    {
        {
            new OpenApiSecurityScheme
            {
                Reference = new OpenApiReference
                {
                    Type = ReferenceType.SecurityScheme,
                    Id = "Bearer"
                }
            },
            new string[] {}
        }
    });
});

var app = builder.Build();

// Ejecutar migraciones y seed al iniciar
using (var scope = app.Services.CreateScope())
{
    var services = scope.ServiceProvider;
    try
    {
        var context = services.GetRequiredService<AuthDbContext>();

        // Ejecutar migraciones pendientes
        context.Database.Migrate();

        // Seed de usuario admin si no existe
        if (!context.Users.Any(u => u.Email == "admin@gmail.com"))
        {
            var adminUser = new AuthService.Domain.Entities.User(
                "admin@gmail.com",
                BCrypt.Net.BCrypt.HashPassword("admin")
            );
            context.Users.Add(adminUser);
            await context.SaveChangesAsync();

            var logger = services.GetRequiredService<ILogger<Program>>();
            logger.LogInformation("Usuario admin creado exitosamente");
        }
    }
    catch (Exception ex)
    {
        var logger = services.GetRequiredService<ILogger<Program>>();
        logger.LogError(ex, "Error durante la inicialización de la base de datos");
    }
}

if (app.Environment.IsDevelopment())
{
    app.UseSwagger();
    app.UseSwaggerUI();
}

app.UseHttpsRedirection();

app.UseAuthentication();
app.UseAuthorization();

app.MapAuthEndpoints();

app.MapGet("/api/protected", () => "Este es un endpoint protegido!")
    .RequireAuthorization()
    .WithName("ProtectedEndpoint")
    .WithOpenApi();

app.Run();
