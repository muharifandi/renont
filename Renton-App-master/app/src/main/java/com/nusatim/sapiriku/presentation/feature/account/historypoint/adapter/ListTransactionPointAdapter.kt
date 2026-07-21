package com.nusatim.sapiriku.presentation.feature.account.historypoint.adapter
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.view.isVisible
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.RecyclerView
import com.nusatim.sapiriku.core.ui.LoadingFooterListAdapter
import com.nusatim.sapiriku.core.util.ViewUtils
import com.nusatim.sapiriku.databinding.ItemListPointBinding
import com.nusatim.sapiriku.domain.model.TransactionPoint

class ListTransactionPointAdapter : LoadingFooterListAdapter<TransactionPoint>(DIFF_CALLBACK) {

    override fun onCreateItemViewHolder(parent: ViewGroup): RecyclerView.ViewHolder =
        TransactionPointViewHolder(ItemListPointBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindItemViewHolder(holder: RecyclerView.ViewHolder, item: TransactionPoint, position: Int) {
        (holder as TransactionPointViewHolder).bind(item)
    }

    inner class TransactionPointViewHolder(
        private val binding: ItemListPointBinding
    ) : RecyclerView.ViewHolder(binding.root) {
        fun bind(item: TransactionPoint) {
            binding.txtDate.text = ViewUtils.mysqlDateToNormalDate(item.date.orEmpty(), "yyyy-MM-dd HH:mm:ss", "dd MMM yyyy HH:mm")

            binding.txtDebit.isVisible = item.pointDebit > 0
            if (item.pointDebit > 0) {
                binding.txtDebit.text = "+${item.pointDebit}"
            }

            binding.txtCredit.isVisible = item.pointCredit > 0
            if (item.pointCredit > 0) {
                binding.txtCredit.text = "-${item.pointCredit}"
            }

            binding.txtDescription.text = item.description
        }
    }

    private companion object {
        val DIFF_CALLBACK = object : DiffUtil.ItemCallback<TransactionPoint>() {
            override fun areItemsTheSame(oldItem: TransactionPoint, newItem: TransactionPoint) = oldItem.id == newItem.id
            override fun areContentsTheSame(oldItem: TransactionPoint, newItem: TransactionPoint) = oldItem == newItem
        }
    }
}
