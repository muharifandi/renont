package com.nusatim.sapiriku.core.ui.base

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.viewbinding.ViewBinding
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.core.util.SnackbarPosition
import com.nusatim.sapiriku.core.util.SnackbarType
import com.nusatim.sapiriku.core.util.showSapirikuSnackbar

/**
 * BaseFragment untuk standarisasi seluruh Fragment di aplikasi Sapiriku.
 */
abstract class BaseFragment<VB : ViewBinding> : Fragment() {

    private var _binding: VB? = null
    protected val binding: VB get() = _binding!!

    protected abstract val bindingInflater: (LayoutInflater, ViewGroup?, Boolean) -> VB

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View? {
        _binding = bindingInflater(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        setupUI()
        setupObserver()
    }

    protected open fun setupUI() {}

    protected open fun setupObserver() {}

    // --- Snackbar Helpers ---

    fun showSuccessSnackbar(
        message: String,
        position: SnackbarPosition = SnackbarPosition.BOTTOM
    ) {
        view?.showSapirikuSnackbar(message, SnackbarType.SUCCESS, position)
    }

    fun showErrorSnackbar(
        message: String,
        position: SnackbarPosition = SnackbarPosition.BOTTOM
    ) {
        view?.showSapirikuSnackbar(message, SnackbarType.ERROR, position)
    }

    fun showWarningSnackbar(
        message: String,
        position: SnackbarPosition = SnackbarPosition.BOTTOM
    ) {
        view?.showSapirikuSnackbar(message, SnackbarType.WARNING, position)
    }

    fun showPendingSnackbar(
        message: String,
        position: SnackbarPosition = SnackbarPosition.BOTTOM
    ) {
        view?.showSapirikuSnackbar(message, SnackbarType.PENDING, position)
    }

    // ------------------------

    protected fun showToast(message: String?) {
        message?.let { Toast.makeText(requireContext(), it, Toast.LENGTH_SHORT).show() }
    }

    protected open fun handleUiState(state: UiState<*>, onLoading: (Boolean) -> Unit = {}) {
        onLoading(state is UiState.Loading)
        if (state is UiState.Error) {
            showErrorSnackbar(state.message)
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
