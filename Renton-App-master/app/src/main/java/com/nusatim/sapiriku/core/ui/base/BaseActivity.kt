package com.nusatim.sapiriku.core.ui.base

import android.os.Bundle
import android.view.LayoutInflater
import android.view.MenuItem
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.viewbinding.ViewBinding
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.core.util.SnackbarPosition
import com.nusatim.sapiriku.core.util.SnackbarType
import com.nusatim.sapiriku.core.util.applyExitTransition
import com.nusatim.sapiriku.core.util.showSapirikuSnackbar

/**
 * BaseActivity untuk standarisasi seluruh Activity di aplikasi Sapiriku.
 */
abstract class BaseActivity<VB : ViewBinding> : AppCompatActivity() {

    private var _binding: VB? = null
    protected val binding: VB get() = _binding!!

    protected abstract val bindingInflater: (LayoutInflater) -> VB

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        _binding = bindingInflater(layoutInflater)
        setContentView(binding.root)

        if (useEdgeToEdge) {
            setupEdgeToEdge()
        }

        setupUI()
        setupObserver()
    }

    protected open val useEdgeToEdge: Boolean = false

    protected open fun setupUI() {}

    protected open fun setupObserver() {}

    // --- Snackbar Helpers ---

    fun showSuccessSnackbar(
        message: String,
        position: SnackbarPosition = SnackbarPosition.BOTTOM
    ) {
        binding.root.showSapirikuSnackbar(message, SnackbarType.SUCCESS, position)
    }

    fun showErrorSnackbar(
        message: String,
        position: SnackbarPosition = SnackbarPosition.BOTTOM
    ) {
        binding.root.showSapirikuSnackbar(message, SnackbarType.ERROR, position)
    }

    fun showWarningSnackbar(
        message: String,
        position: SnackbarPosition = SnackbarPosition.BOTTOM
    ) {
        binding.root.showSapirikuSnackbar(message, SnackbarType.WARNING, position)
    }

    fun showPendingSnackbar(
        message: String,
        position: SnackbarPosition = SnackbarPosition.BOTTOM
    ) {
        binding.root.showSapirikuSnackbar(message, SnackbarType.PENDING, position)
    }

    // ------------------------

    protected fun showToast(message: String?) {
        message?.let { Toast.makeText(this, it, Toast.LENGTH_SHORT).show() }
    }

    protected open fun handleUiState(state: UiState<*>, onLoading: (Boolean) -> Unit = {}) {
        onLoading(state is UiState.Loading)
        if (state is UiState.Error) {
            showErrorSnackbar(state.message)
        }
    }

    protected fun setupToolbar(
        toolbar: androidx.appcompat.widget.Toolbar?,
        title: String? = null,
        showBackButton: Boolean = true
    ) {
        setSupportActionBar(toolbar)
        supportActionBar?.apply {
            this.title = title
            setDisplayHomeAsUpEnabled(showBackButton)
            setDisplayShowHomeEnabled(showBackButton)
        }
    }

    private fun setupEdgeToEdge() {}

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
            return true
        }
        return super.onOptionsItemSelected(item)
    }

    override fun onDestroy() {
        super.onDestroy()
        _binding = null
    }

    override fun finish() {
        super.finish()
        applyExitTransition(com.nusatim.sapiriku.R.anim.slide_in_left, com.nusatim.sapiriku.R.anim.slide_out_right)
    }
}
