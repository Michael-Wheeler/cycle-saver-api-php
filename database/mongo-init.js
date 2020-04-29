db.createUser(
  {
    user : "testuser",
    pwd: "pass",
    roles: [
      {
        role: "readwrite",
        db : "users"
      }
    ]
  }
)
db.container.insert({ myfield: 'hello', thatfield: 'testing' })
