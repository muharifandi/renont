package com.rentone.user.core.util

import android.content.Intent
import android.os.Build
import java.io.Serializable

/** Version-safe replacement for the deprecated single-argument `Intent.getSerializableExtra(String)`. */
inline fun <reified T : Serializable> Intent.getSerializableExtraCompat(key: String): T? {
    return if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
        getSerializableExtra(key, T::class.java)
    } else {
        @Suppress("DEPRECATION")
        getSerializableExtra(key) as? T
    }
}
