package com.nusatim.sapiriku.presentation.feature.login

import android.Manifest
import android.app.Activity
import android.content.Intent
import android.net.Uri
import android.view.LayoutInflater
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.core.common.Config
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.core.ui.base.BaseActivity
import com.nusatim.sapiriku.databinding.ActivityLoginBinding
import com.nusatim.sapiriku.presentation.feature.register.customer.RegisterCustomerActivity
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.nusatim.sapiriku.core.util.applyExitTransition

@AndroidEntryPoint
class LoginActivity : BaseActivity<ActivityLoginBinding>() {

    override val bindingInflater: (LayoutInflater) -> ActivityLoginBinding = ActivityLoginBinding::inflate
    private val viewModel: LoginViewModel by viewModels()

    private val requestPermissionsLauncher = registerForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { permissions ->
        if (permissions.all { it.value }) {
            navigateToRegister()
        } else {
            showPermissionDeniedDialog()
        }
    }

    override fun setupUI() {
        setupToolbar(binding.toolbar, getString(R.string.login))
        
        binding.btnLogin.setOnClickListener {
            viewModel.login(
                binding.inputEmail.text.toString(),
                binding.inputPassword.text.toString()
            )
        }

        binding.btnRegister.setOnClickListener {
            requestPermissionsLauncher.launch(
                arrayOf(
                    Manifest.permission.ACCESS_FINE_LOCATION,
                    Manifest.permission.ACCESS_COARSE_LOCATION,
                    Manifest.permission.CAMERA,
                    Manifest.permission.READ_EXTERNAL_STORAGE,
                    Manifest.permission.WRITE_EXTERNAL_STORAGE
                )
            )
        }

        binding.txtForgotPassword.setOnClickListener {
            val intent = Intent(Intent.ACTION_VIEW, Uri.parse("${Config.HOST_URL}auth/forgot_password"))
            startActivity(intent)
        }
    }

    override fun setupObserver() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.loginState.collect { state ->
                    handleUiState(state) { isLoading ->
                        binding.btnLogin.setLoading(isLoading)
                    }
                    
                    if (state is UiState.Success) {
                        setResult(Activity.RESULT_OK)
                        finish()
                    }
                }
            }
        }
    }

    override fun handleUiState(state: UiState<*>, onLoading: (Boolean) -> Unit) {
        super.handleUiState(state, onLoading)
        if (state is UiState.Error) {
            binding.txtErrorMessage.isVisible = true
            binding.txtErrorMessage.text = state.message
        } else if (state !is UiState.Loading) {
            binding.txtErrorMessage.isVisible = false
        }
    }

    private fun navigateToRegister() {
        val intent = Intent(this, RegisterCustomerActivity::class.java)
        startActivity(intent)
        applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
    }

    private fun showPermissionDeniedDialog() {
        AlertDialog.Builder(this)
            .setTitle(R.string.permission)
            .setMessage(R.string.permission_not_granted)
            .setPositiveButton(android.R.string.ok, null)
            .show()
    }
}
