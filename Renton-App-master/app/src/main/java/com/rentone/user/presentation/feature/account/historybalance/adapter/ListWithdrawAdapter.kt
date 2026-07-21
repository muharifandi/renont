package com.rentone.user.presentation.feature.account.historybalance.adapter
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.RecyclerView
import com.rentone.user.R
import com.rentone.user.core.ui.LoadingFooterListAdapter
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.databinding.ItemListWithdrawBinding
import com.rentone.user.domain.model.Withdraw

class ListWithdrawAdapter : LoadingFooterListAdapter<Withdraw>(DIFF_CALLBACK) {

    override fun onCreateItemViewHolder(parent: ViewGroup): RecyclerView.ViewHolder =
        WithdrawViewHolder(ItemListWithdrawBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindItemViewHolder(holder: RecyclerView.ViewHolder, item: Withdraw, position: Int) {
        (holder as WithdrawViewHolder).bind(item)
    }

    inner class WithdrawViewHolder(private val binding: ItemListWithdrawBinding) : RecyclerView.ViewHolder(binding.root) {
        fun bind(item: Withdraw) {
            val ctx = binding.root.context
            binding.txtDate.text = ViewUtils.mysqlDateToNormalDate(item.date.orEmpty(), "yyyy-MM-dd HH:mm:ss", "dd MMM yyyy HH:mm")
            binding.txtStatus.text = item.statusName

            binding.txtCustomerBank.text = if (item.bankName == null) {
                ctx.getString(R.string.bank_info_not_found)
            } else {
                "${item.bankName} - ${item.name}"
            }
            binding.txtValue.text = "Rp. ${ViewUtils.formatCurrency(item.value)},-"
            binding.txtDescription.text = item.description
        }
    }

    private companion object {
        val DIFF_CALLBACK = object : DiffUtil.ItemCallback<Withdraw>() {
            override fun areItemsTheSame(oldItem: Withdraw, newItem: Withdraw) = oldItem.id == newItem.id
            override fun areContentsTheSame(oldItem: Withdraw, newItem: Withdraw) = oldItem == newItem
        }
    }
}
