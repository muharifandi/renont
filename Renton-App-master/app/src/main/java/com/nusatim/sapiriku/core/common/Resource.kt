package com.nusatim.sapiriku.core.common

sealed class Resource<out T> {
    data class Success<out T>(val data: T) : Resource<T>()
    data class Error(val message: String, val code: Int? = null, val throwable: Throwable? = null) : Resource<Nothing>()
    data object Loading : Resource<Nothing>()
    data object Empty : Resource<Nothing>()
}
