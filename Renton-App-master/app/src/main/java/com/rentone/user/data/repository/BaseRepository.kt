package com.rentone.user.data.repository

import com.rentone.user.core.common.Resource
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.flow
import retrofit2.Response

abstract class BaseRepository {

    protected fun <T, R> safeApiCall(
        apiCall: suspend () -> Response<T>,
        map: (T) -> R
    ): Flow<Resource<R>> = flow {
        emit(Resource.Loading)
        try {
            val response = apiCall()
            if (response.isSuccessful) {
                val body = response.body()
                if (body != null) {
                    emit(Resource.Success(map(body)))
                } else {
                    emit(Resource.Error("Empty response body"))
                }
            } else {
                emit(Resource.Error("API error: ${response.code()} ${response.message()}"))
            }
        } catch (e: Exception) {
            emit(Resource.Error(e.localizedMessage ?: "Unknown error", throwable = e))
        }
    }
}
