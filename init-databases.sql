WAITFOR DELAY '00:00:05'
GO

IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'AuthDb')
BEGIN
    CREATE DATABASE AuthDb;
    PRINT 'Database AuthDb created successfully';
END
ELSE
BEGIN
    PRINT 'Database AuthDb already exists';
END
GO

IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'OrderServiceDB')
BEGIN
    CREATE DATABASE OrderServiceDB;
    PRINT 'Database OrderServiceDB created successfully';
END
ELSE
BEGIN
    PRINT 'Database OrderServiceDB already exists';
END
GO
