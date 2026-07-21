package com.nusatim.sapiriku.presentation.feature.customer.transaction
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.viewpager2.adapter.FragmentStateAdapter
import com.google.android.material.tabs.TabLayoutMediator
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.databinding.FragmentCustomerTransactionBinding
import com.nusatim.sapiriku.presentation.feature.customer.transaction.rentvehicle.CustomerRentVehicleTransactionFragment
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class CustomerTransactionFragment : Fragment() {

    private var _binding: FragmentCustomerTransactionBinding? = null
    private val binding get() = _binding!!

    override fun onCreateView(inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?): View {
        _binding = FragmentCustomerTransactionBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        binding.vpFragment.adapter = object : FragmentStateAdapter(this) {
            override fun getItemCount() = 1
            override fun createFragment(position: Int): Fragment = CustomerRentVehicleTransactionFragment()
        }

        TabLayoutMediator(binding.tabMenu, binding.vpFragment) { tab, _ ->
            tab.text = getString(R.string.rent_vehicle)
        }.attach()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
