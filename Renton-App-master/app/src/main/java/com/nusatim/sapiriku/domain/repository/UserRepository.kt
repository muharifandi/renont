package com.nusatim.sapiriku.domain.repository

import com.nusatim.sapiriku.core.database.entity.UserEntity
import kotlinx.coroutines.flow.Flow

interface UserRepository {
    fun getUser(): Flow<UserEntity?>
    suspend fun saveUser(id: Int, key: String)
    suspend fun deleteUser()
}
