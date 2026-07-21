package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable
/** Domain-layer result of a fire-and-forget write operation (save/delete/post/etc). */
@Serializable
data class OperationResult(
    val success: Boolean,
    val message: String?
)
