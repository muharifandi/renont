package com.nusatim.sapiriku.data.repository

import com.nusatim.sapiriku.core.database.dao.UserDao
import com.nusatim.sapiriku.core.database.entity.UserEntity
import com.nusatim.sapiriku.domain.repository.UserRepository
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
