package com.rentone.user.data.repository

import com.rentone.user.core.database.dao.UserDao
import com.rentone.user.core.database.entity.UserEntity
import com.rentone.user.domain.repository.UserRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject
import javax.inject.Singleton

@Singleton
class UserRepositoryImpl @Inject constructor(
    private val userDao: UserDao
) : UserRepository {

    override fun getUser(): Flow<UserEntity?> = userDao.getUser()

    override suspend fun saveUser(id: Int, key: String) {
        userDao.insertUser(UserEntity(id, key))
    }

    override suspend fun deleteUser() {
        userDao.deleteAll()
    }
}
