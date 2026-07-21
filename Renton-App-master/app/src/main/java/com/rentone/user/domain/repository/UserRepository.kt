package com.rentone.user.domain.repository

import com.rentone.user.core.database.entity.UserEntity
import kotlinx.coroutines.flow.Flow

interface UserRepository {
    fun getUser(): Flow<UserEntity?>
    suspend fun saveUser(id: Int, key: String)
    suspend fun deleteUser()
}
