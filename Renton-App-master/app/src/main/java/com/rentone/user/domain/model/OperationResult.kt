package com.rentone.user.domain.model

/** Domain-layer result of a fire-and-forget write operation (save/delete/post/etc). */
data class OperationResult(
    val success: Boolean,
    val message: String?
)
