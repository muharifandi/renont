package com.nusatim.sapiriku.presentation.feature.home

import android.Manifest
import android.content.Intent
import android.view.LayoutInflater
import android.view.ViewGroup
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AlertDialog
import androidx.core.view.isVisible
import androidx.fragment.app.activityViewModels
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.core.ui.base.BaseFragment
import com.nusatim.sapiriku.core.util.ViewUtils
import com.nusatim.sapiriku.databinding.FragmentHomeBinding
import com.nusatim.sapiriku.presentation.feature.login.LoginActivity
import com.nusatim.sapiriku.presentation.feature.register.customer.RegisterCustomerActivity
import com.nusatim.sapiriku.presentation.feature.rentvehicle.listvehicle.RentVehicleListVehicleActivity
import com.nusatim.sapiriku.presentation.feature.rentvehicle.selectregency.RentVehicleSelectRegencyActivity
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch

@AndroidEntryPoint
class HomeFragment : BaseFragment<FragmentHomeBinding>() {

    override val bindingInflater: (LayoutInflater, ViewGroup?, Boolean) -> FragmentHomeBinding =
        FragmentHomeBinding::inflate
    
    private val viewModel: HomeViewModel by activityViewModels()

    private val requestPermissionsLauncher = registerForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { permissions ->
        if (permissions.all { it.value }) {
            startActivity(Intent(requireContext(), RegisterCustomerActivity::class.java))
        } else {
            showPermissionDeniedDialog()
        }
    }

    override fun setupUI() {
        binding.menuRentCarOnly.setOnClickListener {
            val intent = Intent(requireContext(), RentVehicleSelectRegencyActivity::class.java).apply {
                putExtra("param", hashMapOf("functional_type" to "2", "with_driver" to "0"))
            }
            startActivity(intent)
        }

        binding.menuRentCarWithDriver.setOnClickListener {
            val intent = Intent(requireContext(), RentVehicleSelectRegencyActivity::class.java).apply {
                putExtra("param", hashMapOf("functional_type" to "2", "with_driver" to "1"))
            }
            startActivity(intent)
        }

        binding.menuRentMotorcycle.setOnClickListener {
            val intent = Intent(requireContext(), RentVehicleSelectRegencyActivity::class.java).apply {
                putExtra("param", hashMapOf("functional_type" to "1", "with_driver" to "0"))
            }
            startActivity(intent)
        }

        binding.menuRentClosest.setOnClickListener {
            val intent = Intent(requireContext(), RentVehicleListVehicleActivity::class.java).apply {
                putExtra("param", hashMapOf("sort" to "5"))
            }
            startActivity(intent)
        }

        binding.btnLogin.setOnClickListener {
            startActivity(Intent(requireContext(), LoginActivity::class.java))
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

        binding.srLayout.setOnRefreshListener {
            viewModel.fetchHomeData()
        }
    }

    override fun setupObserver() {
        viewLifecycleOwner.lifecycleScope.launch {
            viewLifecycleOwner.repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.homeState.collect { state ->
                    handleUiState(state) { isLoading ->
                        binding.srLayout.isRefreshing = isLoading
                    }
                    
                    if (state is UiState.Success) {
                        val data = state.data
                        binding.mainContainer.isVisible = true
                        binding.mainShimmer.isVisible = false
                        binding.mainShimmer.stopShimmer()
                        
                        if (data.balance != null) {
                            binding.containerBalance.isVisible = true
                            binding.containerLogin.isVisible = false
                            binding.txtBalance.text = ViewUtils.formatCurrency(data.balance.balance)
                            binding.txtPoint.text = data.balance.point.toString()
                            binding.txtReferalCode.text = data.referralCode
                        } else {
                            binding.containerBalance.isVisible = false
                            binding.containerLogin.isVisible = true
                        }
                    } else if (state is UiState.Loading) {
                        binding.mainContainer.isVisible = false
                        binding.mainShimmer.isVisible = true
                        binding.mainShimmer.startShimmer()
                    }
                }
            }
        }
    }

    private fun showPermissionDeniedDialog() {
        AlertDialog.Builder(requireContext())
            .setTitle(R.string.permission)
            .setMessage(R.string.permission_not_granted)
            .setPositiveButton(android.R.string.ok, null)
            .show()
    }
}
