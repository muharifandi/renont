package com.nusatim.sapiriku.domain.repository

import kotlinx.coroutines.flow.Flow

interface SessionRepository {
    val authToken: Flow<String?>
    suspend fun saveAuthToken(token: String)
    suspend fun clearSession()
}
