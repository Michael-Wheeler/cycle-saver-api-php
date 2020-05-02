db.createUser(
  {
    user : "testuser",
    pwd: "pass",
    roles: [
      {
        role: "root",
        db : "users"
      }
    ]
  }
)
