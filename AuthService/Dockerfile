# Build stage
FROM mcr.microsoft.com/dotnet/sdk:8.0 AS build
WORKDIR /src

# Copy solution file
COPY AuthService.sln ./

# Copy project files
COPY src/AuthService.Api/AuthService.Api.csproj src/AuthService.Api/
COPY src/AuthService.Application/AuthService.Application.csproj src/AuthService.Application/
COPY src/AuthService.Domain/AuthService.Domain.csproj src/AuthService.Domain/
COPY src/AuthService.Infrastructure/AuthService.Infrastructure.csproj src/AuthService.Infrastructure/

# Restore dependencies
RUN dotnet restore

# Copy everything else
COPY . .

# Build the application
WORKDIR /src/src/AuthService.Api
RUN dotnet build -c Release -o /app/build

# Publish the application
FROM build AS publish
RUN dotnet publish -c Release -o /app/publish

# Runtime stage
FROM mcr.microsoft.com/dotnet/aspnet:8.0 AS final
WORKDIR /app
EXPOSE 80
EXPOSE 443

COPY --from=publish /app/publish .

ENTRYPOINT ["dotnet", "AuthService.Api.dll"]
